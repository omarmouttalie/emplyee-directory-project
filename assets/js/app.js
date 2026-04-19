document.addEventListener('DOMContentLoaded', () => {
    const autoSubmitSelects = document.querySelectorAll('[data-auto-submit]');
    autoSubmitSelects.forEach((select) => {
        select.addEventListener('change', () => {
            const form = select.closest('form');
            if (form) {
                form.submit();
            }
        });
    });

    const deleteForms = document.querySelectorAll('[data-delete-form]');
    deleteForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const button = form.querySelector('[data-employee-name]');
            const employeeName = button ? button.getAttribute('data-employee-name') : 'this employee';

            if (!window.confirm(`Delete ${employeeName}? This action cannot be undone.`)) {
                event.preventDefault();
            }
        });
    });

    const photoInput = document.querySelector('[data-photo-file]');
    const preview = document.querySelector('[data-photo-preview] img');
    const nameInput = document.querySelector('#full_name');
    const removePhotoInput = document.querySelector('[data-remove-photo]');
    const photoCaption = document.querySelector('[data-photo-caption]');

    if (photoInput && preview && nameInput) {
        let objectUrl = null;

        const avatarForName = (name) => {
            const trimmedName = (name || 'Employee').trim();
            const parts = trimmedName.split(/\s+/).filter(Boolean).slice(0, 2);
            const initials = (parts.map((part) => part.charAt(0).toUpperCase()).join('') || 'ED').slice(0, 2);
            const svg = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240" role="img" aria-label="Employee avatar">
                    <defs>
                        <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#1d4ed8" />
                            <stop offset="100%" stop-color="#020617" />
                        </linearGradient>
                    </defs>
                    <rect width="240" height="240" rx="36" fill="url(#bg)" />
                    <circle cx="198" cy="48" r="34" fill="rgba(125,211,252,0.18)" />
                    <circle cx="52" cy="194" r="52" fill="rgba(59,130,246,0.22)" />
                    <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#e0f2fe" font-family="Arial, sans-serif" font-size="88" font-weight="700">${initials}</text>
                </svg>
            `;

            return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
        };

        const savedPhoto = () => preview.getAttribute('data-saved-photo') || '';

        const releaseObjectUrl = () => {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
        };

        const refreshPreview = () => {
            releaseObjectUrl();

            const selectedFile = photoInput.files && photoInput.files[0] ? photoInput.files[0] : null;

            if (selectedFile) {
                objectUrl = URL.createObjectURL(selectedFile);
                preview.src = objectUrl;

                if (photoCaption) {
                    photoCaption.textContent = `Selected file: ${selectedFile.name}`;
                }

                if (removePhotoInput) {
                    removePhotoInput.checked = false;
                }

                return;
            }

            if (removePhotoInput && removePhotoInput.checked) {
                preview.src = avatarForName(nameInput.value);

                if (photoCaption) {
                    photoCaption.textContent = 'The current photo will be removed when you save this employee.';
                }

                return;
            }

            const persistedPhoto = savedPhoto();
            const fallbackAvatar = avatarForName(nameInput.value);
            preview.src = persistedPhoto || fallbackAvatar;

            if (photoCaption) {
                photoCaption.textContent = persistedPhoto
                    ? 'Current saved image shown below. Upload a new file to replace it.'
                    : 'No uploaded image yet. A generated avatar will be used until you add one.';
            }
        };

        photoInput.addEventListener('change', refreshPreview);
        nameInput.addEventListener('input', refreshPreview);

        if (removePhotoInput) {
            removePhotoInput.addEventListener('change', () => {
                if (removePhotoInput.checked && photoInput.value) {
                    photoInput.value = '';
                }

                refreshPreview();
            });
        }

        refreshPreview();
    }

    if (window.location.hash === '#employee-form-panel') {
        const fullNameField = document.querySelector('#full_name');
        if (fullNameField) {
            fullNameField.focus();
        }
    }
});
