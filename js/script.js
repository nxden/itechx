// wp-content/plugins/itechx-stats/js/script.js

document.addEventListener('DOMContentLoaded', function () {
    // Получаем форму загрузки CSV
    const csvUploadForm = document.querySelector("#csv-upload-form");

    // Добавляем обработчик событий при отправке формы
    if (csvUploadForm) {
        csvUploadForm.addEventListener("submit", function (event) {
            // Получаем выбранный файл
            const csvFileInput = document.querySelector("#csv-file-input");
            const file = csvFileInput.files[0];

            // Проверяем, выбран ли файл и является ли он файлом CSV
            if (!file || file.type !== "text/csv") {
                alert("Пожалуйста, выберите файл CSV.");
                event.preventDefault(); // Останавливаем отправку формы
            }
        });
    }
});
