// ymaps.ready(init);

// function init(){
//     var myMap = new ymaps.Map("map", {
//         center: [55.76, 37.64], // Можно изменить на центральные координаты, откуда будут чаще всего данные
//         zoom: 5
//     });

//     fetch('mapDB.php')  // Убедитесь, что URL соответствует настройке вашего локального сервера
//     .then(response => response.json())
//     .then(data => {
//         data.forEach(fire => {
//             var placemark = new ymaps.Placemark([fire.latitude, fire.longitude], {
//                 hintContent: 'Место пожара',
//                 balloonContent: 'Координаты пожара: ' + fire.latitude + ', ' + fire.longitude
//             });
//             myMap.geoObjects.add(placemark);
//         });
//     })
//     .catch(error => console.error('Error:', error));
// }




ymaps.ready(init);

function init() {
    var myMap = new ymaps.Map("map", {
        center: [55.76, 37.64], // Центральные координаты
        zoom: 5
    });

    const yearSelect = document.getElementById('year-select');
    const confirmButton = document.getElementById('confirm-button');

    // Загрузить список годов и добавить их в меню
    fetch('mapDB.php?action=getYears')
        .then(response => response.json())
        .then(years => {
            years.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Ошибка загрузки списка годов:', error));

    // Функция для загрузки данных на карту
    function loadMapData(year) {
        let url = 'mapDB.php';
        if (year) {
            url += `?year=${year}`; // Добавляем параметр года к запросу
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                myMap.geoObjects.removeAll(); // Удалить старые метки
                data.forEach(fire => {
                    var placemark = new ymaps.Placemark([fire.latitude, fire.longitude], {
                        hintContent: 'Место пожара',
                        balloonContent: `Координаты пожара: ${fire.latitude}, ${fire.longitude}`
                    });
                    myMap.geoObjects.add(placemark);
                });
            })
            .catch(error => console.error('Ошибка загрузки данных:', error));
    }

    // Загрузить все данные при первой загрузке
    loadMapData();

    // Обработчик кнопки подтверждения выбора года
    confirmButton.addEventListener('click', () => {
        console.log(yearSelect.value)
        const selectedYear = yearSelect.value; // Получить выбранное значение
        loadMapData(selectedYear); // Загрузить данные для выбранного года
    });
}






// ymaps.ready(init);

// function init() {
//     // Инициализация карты
//     var myMap = new ymaps.Map("map", {
//         center: [55.76, 37.64], // Центральные координаты
//         zoom: 5
//     });

//     // Загрузка данных о пожарах
//     fetch('mapDB.php') // Убедитесь, что файл существует
//         .then(response => {
//             // Проверяем, что ответ от сервера успешный
//             if (!response.ok) {
//                 throw new Error(`Ошибка HTTP: ${response.status}`);
//             }
//             return response.json();
//         })
//         .then(data => {
//             // Перебираем данные и создаем метки на карте
//             data.forEach(fire => {
//                 if (isValidCoordinates(fire.latitude, fire.longitude)) {
//                     var placemark = createPlacemark(fire.latitude, fire.longitude);
//                     myMap.geoObjects.add(placemark);
//                 } else {
//                     console.warn("Неверные данные:", fire);
//                 }
//             });
//         })
//         .catch(error => console.error('Ошибка при загрузке данных:', error));
// }

// // Проверяем, что координаты корректны
// function isValidCoordinates(latitude, longitude) {
//     return latitude && longitude && !isNaN(latitude) && !isNaN(longitude);
// }

// // Создаем метку
// function createPlacemark(latitude, longitude) {
//     return new ymaps.Placemark(
//         [latitude, longitude],
//         {
//             hintContent: 'Место пожара',
//             balloonContent: `Координаты пожара: ${latitude}, ${longitude}`
//         }
//     );
// }