class RoteiroMap {
    constructor(mapElementId, options = {}) {
        this.mapElementId = mapElementId;
        this.markers = [];
        this.polyline = null;
        this.bounds = new google.maps.LatLngBounds();
        this.activeInfoWindow = null;

        // Default options
        this.options = {
            zoom: 13,
            center: { lat: 0, lng: 0 },
            ...options
        };

        this.markerIcons = {
            'atracao': 'bi-camera',
            'restaurante': 'bi-cup-hot',
            'hotel': 'bi-building',
            'transporte': 'bi-car-front',
            'outro': 'bi-geo'
        };

        this.initMap();
    }

    initMap() {
        this.map = new google.maps.Map(
            document.getElementById(this.mapElementId),
            this.options
        );

        // Adicionar controles personalizados
        this.addCustomControls();
    }

    addCustomControls() {
        const controlDiv = document.querySelector('.map-controls');
        this.map.controls[google.maps.ControlPosition.TOP_RIGHT].push(controlDiv);
    }

    clearMarkers() {
        this.markers.forEach(marker => marker.setMap(null));
        this.markers = [];
        if (this.polyline) {
            this.polyline.setMap(null);
        }
        this.bounds = new google.maps.LatLngBounds();
    }

    addMarker(location, options = {}) {
        if (!location || !location.lat || !location.lng) {
            console.error('Invalid location provided to addMarker');
            return null;
        }
        
        const marker = new google.maps.Marker({
            position: location,
            map: this.map,
            title: options.title || '',
            animation: google.maps.Animation.DROP,
            icon: this.createMarkerIcon(options.type || 'outro')
        });

        if (options.info) {
            const infoWindow = new google.maps.InfoWindow({
                content: this.createInfoWindowContent({
                    ...options,
                    price: options.price ? new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    }).format(options.price) : null
                })
            });

            marker.addListener('click', () => {
                if (this.activeInfoWindow) {
                    this.activeInfoWindow.close();
                }
                infoWindow.open(this.map, marker);
                this.activeInfoWindow = infoWindow;
            });
        }

        this.markers.push(marker);
        this.bounds.extend(location);
        return marker;
    }

    createMarkerIcon(type) {
        const icon = this.markerIcons[type] || this.markerIcons.outro;
        return {
            url: `data:image/svg+xml,${encodeURIComponent(this.getMarkerSvg(icon))}`,
            scaledSize: new google.maps.Size(32, 32),
            anchor: new google.maps.Point(16, 32)
        };
    }

    getMarkerSvg(icon) {
        return `
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="48" viewBox="0 0 32 48">
            <path fill="#1D3557" d="M16 0C7.2 0 0 7.2 0 16c0 14.4 16 32 16 32s16-17.6 16-32c0-8.8-7.2-16-16-16z"/>
            <path fill="#ffffff" d="${this.getIconPath(icon)}" transform="translate(8,8)"/>
        </svg>`;
    }

    getIconPath(icon) {
        // Simplified paths for Bootstrap Icons
        const paths = {
            'bi-camera': 'M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H2z',
            'bi-cup-hot': 'M4 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm0-5a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm12 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-4 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z',
            'bi-building': 'M4 0h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zm0 1v12h8V1H4z',
            'bi-car-front': 'M4 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm10 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2H6z',
            'bi-geo': 'M8 1a3 3 0 1 0 0 6 3 3 0 0 0 0-6zM4 4a4 4 0 1 1 4.5 3.969V13.5a.5.5 0 0 1-1 0V7.97A4 4 0 0 1 4 4z'
        };
        return paths[icon] || paths['bi-geo'];
    }

    createInfoWindowContent(options) {
        let content = `<div class="map-popup">`;
        
        if (options.image) {
            content += `<div class="popup-header">
                <img src="${options.image}" alt="${options.title}">
            </div>`;
        }
        
        content += `<div class="popup-content">
            <h6 class="popup-title">${options.title}</h6>`;
            
        if (options.address) {
            content += `<p class="popup-details">
                <i class="bi bi-geo-alt me-1"></i>${options.address}
            </p>`;
        }
        
        if (options.price) {
            content += `<p class="popup-details price">
                <i class="bi bi-currency-exchange me-1"></i>${options.price}
            </p>`;
        }
        
        if (options.schedule) {
            content += `<p class="popup-details">
                <i class="bi bi-clock me-1"></i>${options.schedule}
            </p>`;
        }
        
        content += `</div>`;
        
        if (options.actionButtons) {
            content += `<div class="popup-actions">
                ${options.actionButtons}
            </div>`;
        }
        
        content += `</div>`;
        return content;
    }

    updatePolyline(locations) {
        if (this.polyline) {
            this.polyline.setMap(null);
        }

        const path = locations.map(loc => new google.maps.LatLng(loc.lat, loc.lng));
        
        this.polyline = new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: '#457B9D',
            strokeOpacity: 0.8,
            strokeWeight: 3
        });

        this.polyline.setMap(this.map);
    }

    fitBounds() {
        if (this.markers.length > 0) {
            this.map.fitBounds(this.bounds);
            if (this.markers.length === 1) {
                this.map.setZoom(15);
            }
        }
    }

    panTo(location) {
        this.map.panTo(location);
    }
}

class PlaceSearch {
    constructor(options = {}) {
        this.searchInput = document.querySelector(options.searchInputSelector);
        this.resultsContainer = document.querySelector(options.resultsContainerSelector);
        this.searchEndpoint = options.searchEndpoint;
        this.onPlaceSelect = options.onPlaceSelect;
        this.debounceTimeout = null;
        this.lastQuery = '';

        this.initSearchInput();
    }

    initSearchInput() {
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(this.debounceTimeout);
            const query = e.target.value.trim();

            if (query === this.lastQuery) return;
            this.lastQuery = query;

            if (query.length < 3) {
                this.showEmptyState();
                return;
            }

            this.debounceTimeout = setTimeout(() => {
                this.searchPlaces(query);
            }, 500);
        });
    }

    async searchPlaces(query) {
        try {
            this.showLoadingState();
            
            const response = await fetch(`${this.searchEndpoint}?query=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message);
            }

            this.displayResults(data.places);
        } catch (error) {
            console.error('Erro na busca:', error);
            this.showError(error.message);
        }
    }

    displayResults(places) {
        if (!places || places.length === 0) {
            this.showEmptyState('Nenhum lugar encontrado');
            return;
        }

        const resultsHtml = places.map(place => this.createPlaceResultHtml(place)).join('');
        this.resultsContainer.innerHTML = resultsHtml;

        // Adicionar eventos aos botões
        this.resultsContainer.querySelectorAll('.btn-add').forEach(btn => {
            btn.addEventListener('click', () => {
                const placeData = JSON.parse(btn.dataset.place);
                if (this.onPlaceSelect) {
                    this.onPlaceSelect(placeData);
                }
            });
        });
    }

    createPlaceResultHtml(place) {
        return `
            <div class="place-result">
                <div class="d-flex align-items-start">
                    ${place.photo_url ? `
                        <img src="${place.photo_url}" alt="${place.name}" class="result-image">
                    ` : ''}
                    <div class="flex-grow-1">
                        <small class="place-type">${place.type}</small>
                        <h6 class="mb-1">${place.name}</h6>
                        <small class="text-muted d-block">
                            <i class="bi bi-geo-alt me-1"></i>${place.address}
                        </small>
                        ${place.rating ? `
                            <div class="place-rating">
                                <i class="bi bi-star-fill me-1"></i>${place.rating}
                            </div>
                        ` : ''}
                    </div>
                    <button class="btn btn-sm btn-outline-primary btn-add" 
                            data-place='${JSON.stringify(place)}'>
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>`;
    }

    showLoadingState() {
        this.resultsContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Buscando...</span>
                </div>
                <p class="mt-2 text-muted">Buscando lugares...</p>
            </div>`;
    }

    showEmptyState(message = 'Digite um local para buscar') {
        this.resultsContainer.innerHTML = `
            <div class="empty-results">
                <i class="bi bi-search"></i>
                <p>${message}</p>
                <small class="text-muted">Ex: restaurantes, hotéis, pontos turísticos...</small>
            </div>`;
    }

    showError(message) {
        this.resultsContainer.innerHTML = `
            <div class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-circle display-4"></i>
                <p class="mt-2">${message}</p>
                <button class="btn btn-sm btn-outline-danger mt-2" onclick="window.location.reload()">
                    Tentar novamente
                </button>
            </div>`;
    }
}

class RoteiroManager {
    constructor(options = {}) {
        this.map = options.map;
        this.placeSearch = new PlaceSearch({
            searchInputSelector: '#placeSearch',
            resultsContainerSelector: '#searchResults',
            searchEndpoint: '/api/search_places.php',
            onPlaceSelect: (place) => this.addPlaceToDay(place)
        });
        
        this.activeDayId = null;
        this.days = new Map(); // dayId -> array of places
        this.currency = options.currency || 'USD';
        
        this.initSortable();
        this.initEventListeners();
        this.initExchangeRate();
    }

    initSortable() {
        document.querySelectorAll('.list-group').forEach(listGroup => {
            new Sortable(listGroup, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'dragging',
                onStart: (evt) => {
                    evt.item.classList.add('dragging');
                },
                onEnd: (evt) => {
                    evt.item.classList.remove('dragging');
                    this.handlePlaceReorder(evt);
                }
            });
        });
    }

    async initExchangeRate() {
        try {
            const response = await fetch(`/api/get_exchange_rate.php?currency=${this.currency}`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message);
            }

            this.updateExchangeRateDisplay(data.exchange_rate);
            
            // Atualizar a cada hora
            setInterval(() => this.updateExchangeRate(), 3600000);

        } catch (error) {
            console.error('Erro ao obter taxa de câmbio:', error);
            this.showExchangeRateError();
        }
    }

    updateExchangeRateDisplay(rate) {
        const container = document.getElementById('exchangeRate');
        if (!container) return;

        container.innerHTML = `
            <div class="info-card">
                <h6 class="card-title">Taxa de Câmbio</h6>
                <div class="currency-item">
                    <div class="d-flex align-items-center">
                        <img src="https://flagcdn.com/w20/${rate.flag}.png" 
                             alt="${rate.to}" 
                             class="currency-flag">
                        <span>${rate.formatted.direct}</span>
                    </div>
                </div>
                <div class="currency-item">
                    <div class="d-flex align-items-center">
                        <img src="https://flagcdn.com/w20/br.png" 
                             alt="BRL" 
                             class="currency-flag">
                        <span>${rate.formatted.inverse}</span>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">
                    Última atualização: ${this.formatDateTime(rate.last_update)}
                </small>
            </div>`;
    }

    showExchangeRateError() {
        const container = document.getElementById('exchangeRate');
        if (!container) return;

        container.innerHTML = `
            <div class="info-card">
                <h6 class="card-title">Taxa de Câmbio</h6>
                <div class="text-center text-muted">
                    <i class="bi bi-exclamation-circle"></i>
                    <p class="mb-0">Não foi possível carregar as taxas de câmbio</p>
                </div>
            </div>`;
    }

    formatDateTime(dateStr) {
        const date = new Date(dateStr);
        return new Intl.DateTimeFormat('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    }

    async updateExchangeRate() {
        try {
            const response = await fetch(`/api/get_exchange_rate.php?currency=${this.currency}`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message);
            }

            this.updateExchangeRateDisplay(data.exchange_rate);

        } catch (error) {
            console.error('Erro ao atualizar taxa de câmbio:', error);
        }
    }

    initEventListeners() {
        // Day tabs
        document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (e) => {
                const dayId = e.target.dataset.dayId;
                this.setActiveDay(dayId);
            });
        });

        // Remove place buttons
        document.querySelectorAll('.remove-place').forEach(btn => {
            btn.addEventListener('click', (e) => this.handlePlaceRemove(e));
        });

        // Map control buttons
        document.querySelector('.map-control-btn[data-action="center"]')?.addEventListener('click', 
            () => this.centerMap());
        document.querySelector('.map-control-btn[data-action="fit"]')?.addEventListener('click', 
            () => this.fitMapToBounds());
    }

    async handlePlaceReorder(evt) {
        const dayId = evt.to.dataset.dayId;
        const items = evt.to.children;
        const newOrder = {};
        
        Array.from(items).forEach((item, index) => {
            newOrder[item.dataset.id] = index + 1;
        });
        
        try {
            const response = await fetch('/api/reorder_places.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    dayId: dayId,
                    order: newOrder,
                    csrf_token: document.querySelector('meta[name="csrf-token"]').content
                })
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message);
            }

            // Atualizar os locais no mapa e na lista
            this.days.set(dayId, data.places);
            this.updateMapForDay(dayId);

            // Feedback visual
            evt.to.classList.add('reorder-success');
            setTimeout(() => evt.to.classList.remove('reorder-success'), 500);

        } catch (error) {
            console.error('Erro ao atualizar ordem:', error);
            alert('Erro ao atualizar a ordem dos locais. Tente novamente.');
            
            // Reverter a ordem visual
            const list = evt.to;
            const items = Array.from(list.children);
            items.sort((a, b) => a.dataset.order - b.dataset.order);
            items.forEach(item => list.appendChild(item));
            
            // Feedback visual
            evt.to.classList.add('reorder-error');
            setTimeout(() => evt.to.classList.remove('reorder-error'), 500);
        }
    }

    async handlePlaceRemove(e) {
        const item = e.target.closest('.list-group-item');
        const localId = item.dataset.id;
        const dayId = item.closest('.list-group').dataset.dayId;
        
        if (!confirm('Tem certeza que deseja remover este local?')) {
            return;
        }
        
        try {
            // Efeito de fade out
            item.style.transition = 'opacity 0.3s ease-out';
            item.style.opacity = '0';
            
            const response = await fetch('/api/delete_place.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    placeId: localId,
                    csrf_token: document.querySelector('meta[name="csrf-token"]').content
                })
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message);
            }

            // Remover o item após o fade out
            setTimeout(() => {
                item.remove();
                this.updateMapForDay(dayId);
            }, 300);

        } catch (error) {
            console.error('Erro ao remover local:', error);
            // Restaurar a opacidade em caso de erro
            item.style.opacity = '1';
            alert('Erro ao remover o local. Tente novamente.');
        }
    }

    async addPlaceToDay(place) {
        if (!this.activeDayId) {
            alert('Selecione um dia para adicionar o local');
            return;
        }

        try {
            const response = await fetch('/api/add_place.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    dayId: this.activeDayId,
                    place: place,
                    csrf_token: document.querySelector('meta[name="csrf-token"]').content
                })
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message);
            }

            // Adicionar o local à lista
            const listGroup = document.querySelector(`.list-group[data-day-id="${this.activeDayId}"]`);
            const newItem = this.createPlaceListItem(data.place);
            listGroup.appendChild(newItem);

            // Atualizar o mapa
            this.updateMapForDay(this.activeDayId);

        } catch (error) {
            console.error('Erro ao adicionar local:', error);
            alert('Erro ao adicionar o local. Tente novamente.');
        }
    }

    createPlaceListItem(place) {
        const item = document.createElement('div');
        item.className = 'list-group-item d-flex align-items-center';
        item.dataset.id = place.id;
        item.dataset.order = place.ordem;
        
        item.innerHTML = `
            <i class="bi bi-grip-vertical drag-handle"></i>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">
                            <i class="bi ${this.getTypeIcon(place.tipo)} me-2"></i>
                            ${place.nome}
                        </h6>
                        ${place.hora_inicio ? `
                            <span class="time-badge">
                                <i class="bi bi-clock me-1"></i>
                                ${this.formatTime(place.hora_inicio)} - 
                                ${this.formatTime(place.hora_fim)}
                            </span>
                        ` : ''}
                    </div>
                    <i class="bi bi-x-lg remove-place"></i>
                </div>
            </div>`;

        item.querySelector('.remove-place').addEventListener('click', 
            (e) => this.handlePlaceRemove(e));

        return item;
    }

    getTypeIcon(type) {
        const icons = {
            'atracao': 'bi-camera',
            'restaurante': 'bi-cup-hot',
            'hotel': 'bi-building',
            'transporte': 'bi-car-front',
            'outro': 'bi-geo'
        };
        return icons[type] || icons.outro;
    }

    formatTime(time) {
        return time ? time.substr(0, 5) : '';
    }

    setActiveDay(dayId) {
        this.activeDayId = dayId;
        this.updateMapForDay(dayId);
    }

    async updateMapForDay(dayId) {
        try {
            const response = await fetch(`/api/get_day_places.php?dayId=${dayId}`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message);
            }

            this.days.set(dayId, data.places);
            this.updateMap(data.places);

        } catch (error) {
            console.error('Erro ao carregar locais do dia:', error);
            alert('Erro ao atualizar o mapa. Tente novamente.');
        }
    }

    updateMap(places) {
        this.map.clearMarkers();

        places.forEach(place => {
            if (place.latitude && place.longitude) {
                this.map.addMarker(
                    { lat: parseFloat(place.latitude), lng: parseFloat(place.longitude) },
                    {
                        title: place.nome,
                        type: place.tipo,
                        info: true,
                        address: place.endereco,
                        price: place.preco,
                        schedule: place.hora_inicio ? 
                            `${this.formatTime(place.hora_inicio)} - ${this.formatTime(place.hora_fim)}` : 
                            null
                    }
                );
            }
        });

        // Update polyline
        const locations = places
            .filter(p => p.latitude && p.longitude)
            .map(p => ({ 
                lat: parseFloat(p.latitude), 
                lng: parseFloat(p.longitude) 
            }));
        
        this.map.updatePolyline(locations);
        this.map.fitBounds();
    }

    centerMap() {
        if (this.map.markers.length > 0) {
            this.map.panTo(this.map.markers[0].getPosition());
        }
    }

    fitMapToBounds() {
        this.map.fitBounds();
    }
}
