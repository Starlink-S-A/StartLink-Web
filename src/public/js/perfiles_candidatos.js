(() => {
  const endpoint = window.PERFILES_CANDIDATOS_ENDPOINT || '';
  const canViewDetails = !!window.PERFILES_CANDIDATOS_CAN_VIEW_DETAILS;
  const userCompanies = Array.isArray(window.PERFILES_USER_COMPANIES) ? window.PERFILES_USER_COMPANIES : [];

  const filterName = document.getElementById('filterName');
  const filterTitle = document.getElementById('filterTitle');
  const filterSkill = document.getElementById('filterSkill');

  const profilesContainer = document.getElementById('profilesContainer');
  const profilesEmptyState = document.getElementById('profilesEmptyState');

  const publishProfileForm = document.getElementById('publishProfileForm');

  const viewProfileModalEl = document.getElementById('viewProfileModal');
  const viewProfileModalBody = document.getElementById('viewProfileModalBody');
  const hireCandidateModalEl = document.getElementById('hireCandidateModal');
  const hireCandidateForm = document.getElementById('hireCandidateForm');
  const hireCandidateIdInput = document.getElementById('hireCandidateId');
  const hireCandidateNameEl = document.getElementById('hireCandidateName');
  const hireCompanySelect = document.getElementById('hireCompanySelect');

  function escapeHtml(value) {
    const str = String(value ?? '');
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function customAlert(message) {
    const body = document.getElementById('alertDialogBody');
    const modalEl = document.getElementById('alertDialog');
    if (!body || !modalEl || !window.bootstrap) return;
    body.textContent = String(message ?? '');
    new window.bootstrap.Modal(modalEl).show();
  }

  function debounce(fn, waitMs) {
    let t = null;
    return (...args) => {
      if (t) clearTimeout(t);
      t = setTimeout(() => fn(...args), waitMs);
    };
  }

  function renderSkillsFromString(skillsRaw) {
    const raw = String(skillsRaw ?? '').trim();
    if (!raw) return '';
    const skills = raw
      .split(',')
      .map(s => s.trim())
      .filter(Boolean)
      .slice(0, 6);
    return renderSkillsFromArray(skills);
  }

  function renderSkillsFromArray(skills) {
    const list = Array.isArray(skills) ? skills.filter(Boolean).slice(0, 8) : [];
    if (!list.length) return '';
    return `
      <div class="d-flex flex-wrap gap-2 mt-2">
        ${list.map(s => `<span class="skill-chip">${escapeHtml(s)}</span>`).join('')}
      </div>
    `;
  }

  function renderProfileCard(profile) {
    const locationParts = [
      profile.ciudad || '',
      profile.departamento || '',
      profile.pais || '',
    ].filter(Boolean);
    const location = locationParts.join(', ');

    return `
      <div class="col-lg-6">
        <div class="dash-card h-100 p-4">
          <div class="d-flex gap-3 align-items-start">
            <img class="rounded-circle border profile-card-avatar" src="${escapeHtml(profile.foto_url || '')}" alt="Foto">
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between gap-2">
                <div>
                  <h5 class="fw-700 mb-1">${escapeHtml(profile.nombre || '')}</h5>
                  <div class="text-primary fw-600 small mb-1">${escapeHtml(profile.titulo_buscado || '')}</div>
                  <div class="text-muted small">${escapeHtml(location)}</div>
                </div>
                ${
                  canViewDetails
                    ? `<button class="btn btn-sm btn-outline-primary view-profile-btn" data-candidate-id="${escapeHtml(profile.id_usuario)}">Ver completo</button>`
                    : ''
                }
              </div>
              ${renderSkillsFromString(profile.habilidades)}
            </div>
          </div>
        </div>
      </div>
    `;
  }

  async function fetchProfiles() {
    if (!endpoint) return;

    const params = new URLSearchParams();
    params.set('ajax', 'search');
    params.set('name', filterName?.value || '');
    params.set('title', filterTitle?.value || '');
    params.set('skill', filterSkill?.value || '');

    const url = `${endpoint}?${params.toString()}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Error');
    return data.profiles || [];
  }

  async function refreshProfiles() {
    try {
      const profiles = await fetchProfiles();
      if (!profilesContainer || !profilesEmptyState) return;

      if (!profiles.length) {
        profilesContainer.classList.add('d-none');
        profilesEmptyState.classList.remove('d-none');
        profilesContainer.innerHTML = '';
        return;
      }

      profilesEmptyState.classList.add('d-none');
      profilesContainer.classList.remove('d-none');
      profilesContainer.innerHTML = profiles.map(renderProfileCard).join('');

      bindDetailButtons();
    } catch (e) {
      customAlert('No se pudieron cargar los perfiles con los filtros actuales.');
    }
  }

  async function fetchProfileDetail(candidateId) {
    if (!endpoint) return null;
    const params = new URLSearchParams();
    params.set('ajax', 'profile');
    params.set('candidate_id', String(candidateId));
    const res = await fetch(`${endpoint}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Error');
    return data.profile;
  }

  function renderProfileDetail(profile) {
    const locationParts = [
      profile.ciudad || '',
      profile.departamento || '',
      profile.pais || '',
    ].filter(Boolean);
    const location = locationParts.join(', ');

    const salary =
      profile.expectativa_salarial === null || profile.expectativa_salarial === '' || typeof profile.expectativa_salarial === 'undefined'
        ? 'No especificada'
        : `$${Number(profile.expectativa_salarial).toLocaleString('es-CO', { maximumFractionDigits: 0 })}`;

    const skillsHtml = renderSkillsFromArray(profile.skills || []);

    const experiences = Array.isArray(profile.experiences) ? profile.experiences : [];
    const studies = Array.isArray(profile.studies) ? profile.studies : [];

    const expHtml = experiences.length
      ? `<ul class="list-group list-group-flush">
          ${experiences
            .slice(0, 8)
            .map(e => {
              const period = `${escapeHtml(e.fecha_inicio || '')}${e.fecha_fin ? ' – ' + escapeHtml(e.fecha_fin) : ' – Presente'}`;
              return `<li class="list-group-item px-0">
                        <div class="fw-600">${escapeHtml(e.titulo_puesto || '')}</div>
                        <div class="text-muted small">${escapeHtml(e.empresa_nombre || '')}</div>
                        <div class="text-muted small">${period}</div>
                      </li>`;
            })
            .join('')}
        </ul>`
      : `<div class="text-muted small">Sin experiencia registrada.</div>`;

    const studiesHtml = studies.length
      ? `<ul class="list-group list-group-flush">
          ${studies
            .slice(0, 8)
            .map(s => {
              const period = `${escapeHtml(s.fecha_inicio || '')}${s.fecha_fin ? ' – ' + escapeHtml(s.fecha_fin) : ''}`;
              return `<li class="list-group-item px-0">
                        <div class="fw-600">${escapeHtml(s.titulo_grado || '')}</div>
                        <div class="text-muted small">${escapeHtml(s.institucion || '')}</div>
                        <div class="text-muted small">${period}</div>
                      </li>`;
            })
            .join('')}
        </ul>`
      : `<div class="text-muted small">Sin estudios registrados.</div>`;

    const cvHtml = profile.cv_url
      ? `<a class="btn btn-sm btn-outline-secondary mt-2" href="${escapeHtml(profile.cv_url)}" target="_blank" rel="noopener noreferrer">Ver CV</a>`
      : '';

    const hireSection = canViewDetails && userCompanies.length
      ? `
        <div class="mt-4 d-flex justify-content-end">
          <button type="button" class="btn btn-success btn-sm" id="openHireModalBtn" data-candidate-id="${escapeHtml(profile.id)}" data-candidate-name="${escapeHtml(profile.nombre || '')}">
            Contratar
          </button>
        </div>
      `
      : '';

    return `
      <div class="row align-items-center g-4">
        <div class="col-md-4 text-center">
          <img src="${escapeHtml(profile.foto_url || '')}" class="img-fluid rounded-circle border" style="max-width: 150px; height: 150px; object-fit: cover;" alt="Foto">
          <h4 class="fw-700 mt-3 mb-1">${escapeHtml(profile.nombre || '')}</h4>
          <div class="text-muted small">${escapeHtml(profile.email || '')}</div>
          ${profile.telefono ? `<div class="text-muted small">${escapeHtml(profile.telefono)}</div>` : ''}
          ${profile.cargo ? `<div class="text-muted small">${escapeHtml(profile.cargo)}</div>` : ''}
          ${cvHtml}
        </div>
        <div class="col-md-8">
          <div class="mb-2"><span class="text-muted small">Título buscado</span><div class="fw-600">${escapeHtml(profile.titulo_buscado || '')}</div></div>
          <div class="row g-3">
            <div class="col-sm-6">
              <span class="text-muted small">Tipo de contrato</span>
              <div class="fw-600">${escapeHtml(profile.tipo_contrato_preferido || '')}</div>
            </div>
            <div class="col-sm-6">
              <span class="text-muted small">Modalidad</span>
              <div class="fw-600">${escapeHtml(profile.modalidad_preferida || '')}</div>
            </div>
            <div class="col-sm-6">
              <span class="text-muted small">Expectativa salarial</span>
              <div class="fw-600">${escapeHtml(salary)}</div>
            </div>
            <div class="col-sm-6">
              <span class="text-muted small">Ubicación</span>
              <div class="fw-600">${escapeHtml(location)}</div>
            </div>
          </div>
          <div class="mt-3">
            <span class="text-muted small">Habilidades</span>
            ${skillsHtml || `<div class="text-muted small">Sin habilidades registradas.</div>`}
          </div>
          <div class="mt-4">
            <div class="fw-600 mb-2">Experiencia laboral</div>
            ${expHtml}
          </div>
          <div class="mt-4">
            <div class="fw-600 mb-2">Estudios académicos</div>
            ${studiesHtml}
          </div>
          ${hireSection}
        </div>
      </div>
    `;
  }

  function populateHireCompanies() {
    if (!hireCompanySelect) return;
    hireCompanySelect.innerHTML = '<option value="">Selecciona una empresa...</option>';
    userCompanies.forEach(c => {
      const opt = document.createElement('option');
      opt.value = String(c.id_empresa);
      opt.textContent = String(c.nombre_empresa);
      hireCompanySelect.appendChild(opt);
    });
  }

  function openHireModal(candidateId, candidateName) {
    if (!hireCandidateModalEl || !window.bootstrap) return;
    if (!hireCandidateIdInput || !hireCandidateNameEl) return;

    populateHireCompanies();
    hireCandidateIdInput.value = String(candidateId);
    hireCandidateNameEl.textContent = String(candidateName || '—');

    const modal = new window.bootstrap.Modal(hireCandidateModalEl);
    modal.show();
  }

  function bindDetailButtons() {
    if (!canViewDetails) return;
    document.querySelectorAll('.view-profile-btn').forEach(btn => {
      if (btn.dataset.bound === '1') return;
      btn.dataset.bound = '1';
      btn.addEventListener('click', async () => {
        const candidateId = btn.getAttribute('data-candidate-id');
        if (!candidateId) return;

        if (!viewProfileModalEl || !viewProfileModalBody || !window.bootstrap) return;
        const modal = new window.bootstrap.Modal(viewProfileModalEl);
        viewProfileModalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
        modal.show();

        try {
          const profile = await fetchProfileDetail(candidateId);
          viewProfileModalBody.innerHTML = renderProfileDetail(profile);
          const openHireBtn = document.getElementById('openHireModalBtn');
          if (openHireBtn) {
            openHireBtn.addEventListener('click', () => {
              openHireModal(openHireBtn.dataset.candidateId, openHireBtn.dataset.candidateName);
            });
          }
        } catch (e) {
          viewProfileModalBody.innerHTML = '<div class="alert alert-danger mb-0">No se pudo cargar el perfil completo.</div>';
        }
      });
    });
  }

  const debouncedRefresh = debounce(refreshProfiles, 250);
  [filterName, filterTitle, filterSkill].forEach(el => {
    if (!el) return;
    el.addEventListener('input', debouncedRefresh);
  });

  if (publishProfileForm) {
    publishProfileForm.addEventListener('submit', async e => {
      e.preventDefault();
      if (!endpoint) return;

      const formData = new FormData(publishProfileForm);
      const body = new URLSearchParams();
      for (const [k, v] of formData.entries()) {
        body.append(k, String(v));
      }

      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
      });

      const data = await res.json().catch(() => null);
      if (!res.ok || !data || !data.success) {
        customAlert(data?.message || 'No se pudo guardar el perfil.');
        return;
      }

      customAlert(data.message || 'Perfil guardado.');

      const modalEl = document.getElementById('publishProfileModal');
      if (modalEl && window.bootstrap) {
        const instance = window.bootstrap.Modal.getInstance(modalEl);
        if (instance) instance.hide();
      }

      refreshProfiles();
    });
  }

  if (hireCandidateForm) {
    hireCandidateForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!endpoint) return;

      const formData = new FormData(hireCandidateForm);
      const body = new URLSearchParams();
      body.set('action', 'hire_candidate');
      for (const [k, v] of formData.entries()) {
        body.set(k, String(v));
      }

      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
      });

      const data = await res.json().catch(() => null);
      if (!res.ok || !data || !data.success) {
        customAlert(data?.message || 'No se pudo contratar al usuario.');
        return;
      }

      customAlert(data.message || 'Contratación registrada.');
      if (hireCandidateModalEl && window.bootstrap) {
        const instance = window.bootstrap.Modal.getInstance(hireCandidateModalEl);
        if (instance) instance.hide();
      }
      refreshProfiles();
    });
  }

  bindDetailButtons();
})();
