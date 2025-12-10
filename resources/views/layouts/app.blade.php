<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Movie Manager')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding-bottom: 50px;
        }
        .navbar {
            background: #2c3e50;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #ecf0f1 !important;
        }
        .navbar-brand:hover {
            color: #3498db !important;
        }
        .search-form {
            max-width: 500px;
        }
        .movie-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 8px;
            overflow: hidden;
            height: 100%;
        }
        .movie-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .movie-poster {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        .movie-title {
            font-weight: 600;
            font-size: 1rem;
            height: 48px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            color: #2c3e50;
        }
        .btn-favorite {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .btn-favorite:hover {
            background: white;
            transform: scale(1.1);
        }
        .btn-favorite.active {
            background: #e74c3c;
            color: white;
        }
        .rating-badge {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: #f39c12;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
        }
        .btn-primary {
            background: #3498db;
            border: none;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }
        .card {
            border: 1px solid #e1e8ed;
            border-radius: 8px;
        }
        .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e1e8ed;
            font-weight: 600;
            color: #2c3e50;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .text-muted {
            color: #6c757d !important;
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                Movie Manager
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="fas fa-home"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('movies.favorites') ? 'active' : '' }}" href="{{ route('movies.favorites') }}">
                            <i class="fas fa-heart"></i> Favoritos
                        </a>
                    </li>
                </ul>
                
                <!-- Search Form -->
                <form action="{{ route('movies.search') }}" method="GET" class="d-flex search-form me-3">
                    <input class="form-control me-2" type="search" name="q" placeholder="Buscar filmes..." value="{{ request('q') }}">
                    <button class="btn btn-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt"></i> Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <!-- Main Content -->
    <main class="container">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuração CSRF para AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Função para exibir toast
        function showToast(message, type = 'success') {
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            const container = document.querySelector('.toast-container');
            container.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = container.lastElementChild;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
        }

        // Função para adicionar/remover favorito
        async function toggleFavorite(tmdbId, button) {
            const isFavorite = button.classList.contains('active');
            
            try {
                const url = isFavorite 
                    ? `/favorites/${button.dataset.favoriteId}`
                    : `/favorites/${tmdbId}`;
                
                const method = isFavorite ? 'DELETE' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    if (isFavorite) {
                        button.classList.remove('active');
                        button.innerHTML = '<i class="far fa-heart"></i>';
                        button.dataset.favoriteId = '';
                    } else {
                        button.classList.add('active');
                        button.innerHTML = '<i class="fas fa-heart"></i>';
                        button.dataset.favoriteId = data.favorite_id;
                    }
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'danger');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao processar requisição', 'danger');
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
