const titleInput = document.getElementById('title');
const authorInput = document.getElementById('author');
const descriptionInput = document.getElementById('description');
const coverInput = document.getElementById('cover');


const previewTitle = document.getElementById('previewTitle');
const previewAuthor = document.getElementById('previewAuthor');
const previewDescription = document.getElementById('previewDescription');
const coverPreviewImg = document.getElementById('coverPreview');


titleInput.addEventListener('input', () => {
    previewTitle.textContent = titleInput.value.trim() || 'Название трека';
});

authorInput.addEventListener('input', () => {
    previewAuthor.textContent = authorInput.value.trim() || 'Автор / Исполнитель';
});

descriptionInput.addEventListener('input', () => {
    previewDescription.textContent =
        descriptionInput.value.trim() || 'Здесь будет краткое описание трека.';
});

// Превью обложки
coverInput.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) {
        coverPreviewImg.src = '';
        coverPreviewImg.alt = 'Обложка';
        return;
    }

    if (!file.type.startsWith('image/')) {
        alert('Пожалуйста, выбери изображение для обложки.');
        this.value = '';
        coverPreviewImg.src = '';
        coverPreviewImg.alt = 'Обложка';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        coverPreviewImg.src = e.target.result;
        coverPreviewImg.alt = file.name;
    };
    reader.readAsDataURL(file);
});
