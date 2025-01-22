
ymaps.ready(init);

function init() {
    var myMap = new ymaps.Map("map", {
        center: [55.76, 37.64], 
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
            url += `?year=${year}`; 
        }


        

        fetch(url)
            .then(response => response.json())
            .then(data => {
                myMap.geoObjects.removeAll(); 
                data.forEach(fire => {
                    var placemark = new ymaps.Placemark([fire.latitude, fire.longitude], {
                        hintContent: 'Место пожара',
                        balloonContent: `Координаты пожара: ${fire.latitude}, ${fire.longitude}`
                    },
                    {
                        
                        iconLayout: 'default#image',
                        
                        iconImageHref: '/png/icon _fire_.png',
                        
                        iconImageSize: [15, 20],
                        
                        iconImageOffset: [-15, -42]
                    }
                    );
                    myMap.geoObjects.add(placemark);
                });
            })
            .catch(error => console.error('Ошибка загрузки данных:', error));
    }

    
    loadMapData();

    
    confirmButton.addEventListener('click', () => {
        console.log(yearSelect.value)
        const selectedYear = yearSelect.value; 
        loadMapData(selectedYear); 
    });
}






