document.addEventListener('DOMContentLoaded', () => {
    const video1 = document.getElementById('video1');
    const video2 = document.getElementById('video2');

    const FADE_DURATION = 1.5; // Segundos de duración del fundido
    const PRE_FADE_OFFSET = 0.5; // Tiempo antes del final para iniciar la transición

    let currentVideo = video1;
    let nextVideo = video2;

    nextVideo.style.opacity = 0;
    nextVideo.style.transition = `opacity ${FADE_DURATION}s ease-in-out`;
    currentVideo.style.transition = `opacity ${FADE_DURATION}s ease-in-out`;

    function crossfadeVideos() {
        if (currentVideo.fadingOut) return;

        currentVideo.fadingOut = true;

        nextVideo.currentTime = 0;
        nextVideo.play().then(() => {
            nextVideo.style.opacity = 1;
            currentVideo.style.opacity = 0;

            setTimeout(() => {
                currentVideo.pause();
                currentVideo.currentTime = 0;
                currentVideo.fadingOut = false;

                // Intercambiar roles
                const temp = currentVideo;
                currentVideo = nextVideo;
                nextVideo = temp;

                // Asegurar que el nuevo "nextVideo" esté oculto
                nextVideo.style.opacity = 0;
            }, FADE_DURATION * 1000);
        }).catch(err => {
            console.error("Error reproduciendo el video de transición:", err);
        });
    }

    function onTimeUpdate() {
        if (this !== currentVideo) return;

        const remainingTime = this.duration - this.currentTime;
        if (remainingTime <= (FADE_DURATION + PRE_FADE_OFFSET) && !currentVideo.fadingOut) {
            crossfadeVideos();
        }
    }

    function onEnded() {
        if (this === currentVideo && !currentVideo.fadingOut) {
            crossfadeVideos(); // Seguridad en caso de no cruzar antes
        }
    }

    [video1, video2].forEach(video => {
        video.addEventListener('timeupdate', onTimeUpdate);
        video.addEventListener('ended', onEnded);
    });

    // Autoplay fallback: Ocultar overlay si existiera
    currentVideo.addEventListener('playing', () => {
        const overlay = document.getElementById('videoOverlay');
        if (overlay) overlay.classList.add('hidden');
    }, { once: true });

    // Iniciar reproducción del primer video
    currentVideo.play().catch(err => {
        console.warn("Autoplay bloqueado. El usuario debe iniciar la reproducción manualmente.");
    });
});
