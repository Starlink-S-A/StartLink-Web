let dialogConfirmCallback = null;

function customConfirm(title, message, iconClass, iconColor, callback) {
    const modalEl = document.getElementById('customDialogModal');
    if (!modalEl) return;
    
    document.getElementById('dialogTitle').innerText = title;
    document.getElementById('dialogMessage').innerText = message;
    
    const iconEl = document.getElementById('dialogIcon');
    iconEl.innerHTML = `<i class="${iconClass}"></i>`;
    iconEl.style.color = iconColor || '#4361ee';
    
    document.getElementById('dialogBtnCancel').classList.remove('d-none');
    
    dialogConfirmCallback = callback;
    
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function customAlert(title, message, iconClass, iconColor, reloadOnClose = false) {
    const modalEl = document.getElementById('customDialogModal');
    if (!modalEl) return;
    
    document.getElementById('dialogTitle').innerText = title;
    document.getElementById('dialogMessage').innerText = message;
    
    const iconEl = document.getElementById('dialogIcon');
    iconEl.innerHTML = `<i class="${iconClass}"></i>`;
    iconEl.style.color = iconColor || '#4361ee';
    
    document.getElementById('dialogBtnCancel').classList.add('d-none');
    
    dialogConfirmCallback = () => {
        if(reloadOnClose) location.reload();
    };
    
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    const btnConfirm = document.getElementById('dialogBtnConfirm');
    if(btnConfirm) {
        btnConfirm.addEventListener('click', () => {
            const modalEl = document.getElementById('customDialogModal');
            const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            bsModal.hide();
            
            if(typeof dialogConfirmCallback === 'function') {
                dialogConfirmCallback();
            }
        });
    }
});

function openUserModal(btnEl, defaultAction = 'view') {
    const payloadStr = btnEl.getAttribute('data-payload');
    const payload = JSON.parse(payloadStr);
    const user = payload.user;
    const isSelf = payload.isSelf;
    
    // Set Avatar
    document.getElementById('modalAvatar').innerText = getInitials(user.nombre);
    document.getElementById('modalAvatar').className = 'admin-modal-avatar avatar-bg-' + (user.id % 4);
    
    // Set Header Info
    document.getElementById('modalUserName').innerText = user.nombre;
    
    // Set Role Badge
    const roleBadge = document.getElementById('modalUserRole');
    roleBadge.innerText = user.nombre_rol;
    roleBadge.className = 'role-badge';
    if(user.id_rol == 1) roleBadge.classList.add('role-admin');
    else if(user.id_rol == 3) roleBadge.classList.add('role-empresa');
    else roleBadge.classList.add('role-candidato');

    // Set Status Badge
    const statusBadge = document.getElementById('modalUserStatus');
    statusBadge.innerHTML = '';
    statusBadge.className = 'status-badge';
    
    if(user.estado_logico === 'Activo') {
        statusBadge.classList.add('status-activo');
        statusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Activo';
    } else if(user.estado_logico === 'Suspendido') {
        statusBadge.classList.add('status-suspendido');
        statusBadge.innerHTML = '<i class="fas fa-ban"></i> Suspendido';
    } else {
        statusBadge.classList.add('status-pendiente');
        statusBadge.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Pendiente';
    }

    // Set Contact Info
    document.getElementById('modalUserEmail').innerText = user.email;
    document.getElementById('modalUserPhone').innerText = user.telefono || 'No registrado';
    
    // Set Company
    if (user.empresas) {
        const empArr = user.empresas.split(', ');
        if (empArr.length > 1) {
            document.getElementById('modalUserCompany').innerText = empArr.length + ' Empresas: ' + user.empresas;
        } else {
            document.getElementById('modalUserCompany').innerText = user.empresas;
        }
    } else {
        document.getElementById('modalUserCompany').innerText = 'Sin empresa';
    }

    // Store user ID for actions
    document.getElementById('currentActionUserId').value = user.id;
    
    // Set Role Select for editing
    document.getElementById('editRoleSelect').value = user.id_rol;

    // Control visibility of admin actions panel based on isSelf
    if (isSelf) {
        document.getElementById('adminActionsPanel').classList.add('hidden');
    } else {
        document.getElementById('adminActionsPanel').classList.remove('hidden');
        // Toggle suspend/activate buttons only if not self
        if(user.estado_logico === 'Suspendido') {
            document.getElementById('btnSuspend').classList.add('hidden');
            document.getElementById('btnActivate').classList.remove('hidden');
        } else {
            document.getElementById('btnSuspend').classList.remove('hidden');
            document.getElementById('btnActivate').classList.add('hidden');
        }
    }

    if (defaultAction === 'edit') {
        document.getElementById('roleEditContainer').classList.remove('hidden');
    } else {
        document.getElementById('roleEditContainer').classList.add('hidden');
    }

    const modal = new bootstrap.Modal(document.getElementById('adminUserModal'));
    modal.show();
}

function directToggleSuspension(userId, actionStr) {
    const theAction = actionStr === 'suspend' ? 'suspender' : 'activar';
    const warningColor = actionStr === 'suspend' ? '#e63946' : '#2a9d8f';
    const warningIcon = actionStr === 'suspend' ? 'fas fa-gavel' : 'fas fa-heartbeat';

    customConfirm("Confirmar Acción", `¿Estás seguro de que quieres ${theAction} a este usuario?`, warningIcon, warningColor, async () => {
        try {
            const res = await fetch('index.php?action=admin_toggle_suspension', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ userId: userId, action: actionStr })
            });
            const data = await res.json();
            if(data.status === 'success') {
                customAlert('¡Hecho!', data.message, 'fas fa-check-circle', '#2a9d8f', true);
            } else {
                customAlert('Ocurrió un error', data.message, 'fas fa-times-circle', '#e63946', false);
            }
        } catch(e) {
            customAlert('Fallo de red', 'Error conectando al servidor.', 'fas fa-wifi', '#e63946', false);
        }
    });
}

function getInitials(name) {
    if(!name) return 'U';
    const parts = name.trim().split(' ');
    if(parts.length > 1) return (parts[0][0] + parts[1][0]).toUpperCase();
    return parts[0][0].toUpperCase();
}

function toggleRoleEdit() {
    const container = document.getElementById('roleEditContainer');
    container.classList.toggle('hidden');
}

function saveRole() {
    const userId = document.getElementById('currentActionUserId').value;
    const newRoleId = document.getElementById('editRoleSelect').value;

    if(!userId || !newRoleId) return;

    customConfirm("Confirmar Acción", "¿Estás seguro de que deseas guardar este nuevo rol?", "fas fa-shield-alt", "#4361ee", async () => {
        try {
            const res = await fetch('index.php?action=admin_update_role', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ userId: userId, newRoleId: newRoleId })
            });
            const data = await res.json();
            if(data.status === 'success') {
                customAlert('¡Guardado!', data.message, 'fas fa-check-circle', '#2a9d8f', true);
            } else {
                customAlert('No se pudo guardar', data.message, 'fas fa-times-circle', '#e63946', false);
            }
        } catch(e) {
            customAlert('Fallo de red', 'Error conectando al servidor.', 'fas fa-wifi', '#e63946', false);
        }
    });
}

function toggleSuspension(actionStr) {
    const userId = document.getElementById('currentActionUserId').value;
    if(!userId) return;

    // We can reuse directToggleSuspension
    directToggleSuspension(userId, actionStr);
}

// Table filtering
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchUsers');
    const roleFilter = document.getElementById('filterRole');
    const statusFilter = document.getElementById('filterStatus');
    const tableRows = document.querySelectorAll('.users-table tbody tr');

    function filterTable() {
        const term = searchInput.value.toLowerCase();
        const role = roleFilter.value;
        const status = statusFilter.value;

        tableRows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const rowRole = row.getAttribute('data-rol');
            const rowStatus = row.getAttribute('data-status');

            const matchSearch = rowText.includes(term);
            const matchRole = (role === 'all' || rowRole == role);
            const matchStatus = (status === 'all' || rowStatus === status);

            if (matchSearch && matchRole && matchStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterTable);
    roleFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
});

function openCompanyModal(btnEl) {
    const payloadStr = btnEl.getAttribute('data-payload');
    const payload = JSON.parse(payloadStr);
    const emp = payload.empresa;
    
    // Set Header
    document.getElementById('modalCompanyName').innerText = emp.nombre_empresa;
    
    const avatarEl = document.getElementById('modalCompanyAvatar');
    if(emp.logo_ruta) {
        avatarEl.style.backgroundImage = "url('assets/images/Uploads/logos_empresa/" + emp.logo_ruta + "')";
        avatarEl.innerText = '';
        avatarEl.className = 'admin-modal-avatar';
    } else {
        avatarEl.style.backgroundImage = 'none';
        avatarEl.innerText = getInitials(emp.nombre_empresa);
        avatarEl.className = 'admin-modal-avatar avatar-bg-' + (emp.id_empresa % 4);
    }
    
    // Set Status
    const statusBadge = document.getElementById('modalCompanyStatus');
    statusBadge.innerHTML = '';
    statusBadge.className = 'status-badge';
    
    if(emp.estado === 'Activa') {
        statusBadge.classList.add('status-activo');
        statusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Activa';
    } else {
        statusBadge.classList.add('status-suspendido');
        statusBadge.innerHTML = '<i class="fas fa-ban"></i> Suspendida';
    }
    
    // Set Contact
    document.getElementById('modalCompanyEmail').innerText = emp.email_contacto || 'No registrado';
    document.getElementById('modalCompanyPhone').innerText = emp.telefono_contacto || 'No registrado';
    
    // Set Location
    let locationStr = [];
    if(emp.ciudad) locationStr.push(emp.ciudad);
    if(emp.departamento) locationStr.push(emp.departamento);
    if(emp.pais) locationStr.push(emp.pais);
    document.getElementById('modalCompanyLocation').innerText = locationStr.length > 0 ? locationStr.join(', ') : 'Desconocida';
    
    // Set Desc
    document.getElementById('modalCompanyDesc').innerText = emp.descripcion || 'Sin descripción provista.';
    
    const modal = new bootstrap.Modal(document.getElementById('adminCompanyModal'));
    modal.show();
}
