<!-- resources/views/map.blade.php -->

<x-guest-layout>
    <head>
        <!-- CSS do Leaflet.js -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <!-- CSS do Tailwind -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
        <!-- CSS do SweetAlert2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    </head>
    <body class="bg-gray-100">
        <div class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg mt-10">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Buscar Contatos</h1>
                <button id="add-contact-btn" class="bg-blue-500 text-white px-4 py-2 rounded">+</button>
            </div>

            <!-- Campo de busca -->
            <form id="search-form" class="mb-4">
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="text" id="search-query" name="query" placeholder="Digite sua busca aqui com pelo menos 3 letras ou '--todos' para ver todos os contatos..." class="w-full p-2 border border-gray-300 rounded mb-4">
            </form>

            <!-- Resultados da busca -->
            <div id="search-results" class="space-y-2"></div>

            <!-- Mapa -->
            <div id="map" class="mt-6 rounded z-10" style="height: 400px; width: 100%;"></div>
        </div>

        <!-- Modal para adicionar novo contato -->
        <div id="add-contact-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-20">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Adicionar Novo Contato</h2>
                <form id="add-contact-form">
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="mb-4">
                        <input type="text" name="name" placeholder="Nome" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="mb-4">
                        <input type="text" name="cpf" placeholder="CPF" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="mb-4">
                        <input type="text" name="phone" placeholder="Telefone" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="mb-4">
                        <input type="text" name="address" placeholder="Endereço" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="mb-4">
                        <input type="text" name="cep" placeholder="CEP" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="mb-4">
                        <input type="text" name="city" placeholder="Cidade" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="mb-4">
                        <input type="text" name="state" placeholder="Estado" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div id="form-errors" class="text-red-500 mb-4"></div>
                    <div class="flex justify-end">
                        <button type="button" id="cancel-add-contact" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancelar</button>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Salvar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inclua o JS do Leaflet.js -->
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        
        <!-- Inclua jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Inclua o JS do SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

        <!-- Inicialização do mapa -->
        <script>
            var map = L.map('map').setView([-25.4284, -49.2733], 12); // Exemplo para Curitiba, ajuste conforme necessário
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            function addMarker(latitude, longitude, name) {
                var marker = L.marker([latitude, longitude]).addTo(map)
                    .bindPopup(name)
                    .openPopup();
                return marker;
            }

            $(document).ready(function() {
                function debounce(func, wait) {
                    let timeout;
                    return function() {
                        const context = this, args = arguments;
                        clearTimeout(timeout);
                        timeout = setTimeout(() => func.apply(context, args), wait);
                    };
                }

                $('#search-query').on('input', debounce(function() {
                    var query = $(this).val();
                    var token = $('input[name="token"]').val();
                    var results = $('#search-results');

                    // Limpar os resultados se a consulta tiver menos de 3 caracteres e não for = a --todos
                    if (query.length < 3 && query !== '--todos') {
                        results.empty();
                        return;
                    }

                    // Lógica para buscar todos os contatos quando a consulta for = a --todos
                    var url = query === '--todos' ? "{{ route('contacts.index') }}" : "{{ route('search') }}";
                    var data = query === '--todos' ? { token: token } : { token: token, query: query };

                    $.ajax({
                        url: url,
                        method: 'GET',
                        data: data,
                        success: function(response) {
                            results.empty();
                            map.eachLayer(function (layer) {
                                if (layer instanceof L.Marker) {
                                    map.removeLayer(layer);
                                }
                            });

                            if (response.length > 0) {
                                response.forEach(function(contact, index) {
                                    var marker = addMarker(contact.latitude, contact.longitude, contact.name);
                                    var resultItem = $(
                                        '<div class="p-4 bg-gray-100 rounded shadow-sm cursor-pointer" data-lat="'+ contact.latitude +'" data-lng="'+ contact.longitude +'">' +
                                            '<h2 class="font-bold">' + contact.name + '</h2>' +
                                            '<p>Telefone: ' + contact.phone + '</p>' +
                                            '<p>Endereço: ' + contact.address + ', ' + contact.city + ', ' + contact.state + '</p>' +
                                        '</div>'
                                    );

                                    resultItem.on('click', function() {
                                        map.setView([contact.latitude, contact.longitude], 15);
                                        marker.openPopup();
                                    });

                                    results.append(resultItem);
                                });
                            } else {
                                results.append('<p class="text-red-500">Nenhum resultado encontrado</p>');
                            }
                        },
                        error: function() {
                            results.html('<p class="text-red-500">Ocorreu um erro</p>');
                        }
                    });
                }, 300));

                // Mostrar modal de adicionar contato
                $('#add-contact-btn').on('click', function() {
                    $('#add-contact-modal').removeClass('hidden');
                });

                // Fechar modal de adicionar contato
                $('#cancel-add-contact').on('click', function() {
                    $('#add-contact-modal').addClass('hidden');
                });

                // Submeter formulário de adicionar contato
                $('#add-contact-form').on('submit', function(event) {
                    event.preventDefault();
                    var token = $('input[name="token"]').val();
                    var formData = $(this).serializeArray();
                    var data = { token: token };

                    formData.forEach(function(field) {
                        data[field.name] = field.value;
                    });

                    $.ajax({
                        url: "{{ route('contacts.store') }}",
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(data),
                        success: function(response) {
                            $('#add-contact-modal').addClass('hidden');
                            $('#form-errors').empty();
                            Swal.fire({
                                icon: 'success',
                                title: 'Contato adicionado com sucesso!',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        },
                        error: function(response) {
                            var errors = response.responseJSON ? response.responseJSON.errors : null;
                            var errorsHtml = '';

                            if (errors) {
                                $.each(errors, function(key, value) {
                                    errorsHtml += '<p>' + value[0] + '</p>';
                                });
                            } else {
                                errorsHtml = '<p>Ocorreu um erro desconhecido. Por favor, tente novamente.</p>';
                            }

                            $('#form-errors').html(errorsHtml);
                        }
                    });
                });
            });
        </script>
    </body>
</x-guest-layout>
