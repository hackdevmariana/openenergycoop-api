<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Bootstrap Icons - OpenEnergyCoop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .icon-demo {
            padding: 20px;
            margin: 10px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .icon-demo:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .icon-demo svg {
            width: 48px;
            height: 48px;
            margin-bottom: 10px;
        }
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .category-title {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin: 30px 0 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    🌱 Test de Iconos Bootstrap - OpenEnergyCoop
                </h1>
                <p class="text-center text-muted mb-5">
                    Demostración de los iconos de Bootstrap disponibles en tu aplicación Laravel
                </p>
            </div>
        </div>

        <!-- Iconos de Energía -->
        <div class="category-title">
            <h2>⚡ Iconos de Energía</h2>
        </div>
        <div class="icon-grid">
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/lightning.svg') }}"/>
                </svg>
                <div><strong>lightning</strong></div>
                <small class="text-muted">Rayo</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/lightning-charge.svg') }}"/>
                </svg>
                <div><strong>lightning-charge</strong></div>
                <small class="text-muted">Carga eléctrica</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/sun.svg') }}"/>
                </svg>
                <div><strong>sun</strong></div>
                <small class="text-muted">Sol</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/wind.svg') }}"/>
                </svg>
                <div><strong>wind</strong></div>
                <small class="text-muted">Viento</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/water.svg') }}"/>
                </svg>
                <div><strong>water</strong></div>
                <small class="text-muted">Agua</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/leaf.svg') }}"/>
                </svg>
                <div><strong>leaf</strong></div>
                <small class="text-muted">Hoja</small>
            </div>
        </div>

        <!-- Iconos de Plantas -->
        <div class="category-title">
            <h2>🌱 Iconos de Plantas</h2>
        </div>
        <div class="icon-grid">
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/tree.svg') }}"/>
                </svg>
                <div><strong>tree</strong></div>
                <small class="text-muted">Árbol</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/flower1.svg') }}"/>
                </svg>
                <div><strong>flower1</strong></div>
                <small class="text-muted">Flor</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/seed.svg') }}"/>
                </svg>
                <div><strong>seed</strong></div>
                <small class="text-muted">Semilla</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/recycle.svg') }}"/>
                </svg>
                <div><strong>recycle</strong></div>
                <small class="text-muted">Reciclar</small>
            </div>
        </div>

        <!-- Iconos de Usuario y Gestión -->
        <div class="category-title">
            <h2>👤 Iconos de Usuario y Gestión</h2>
        </div>
        <div class="icon-grid">
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/person.svg') }}"/>
                </svg>
                <div><strong>person</strong></div>
                <small class="text-muted">Usuario</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/people.svg') }}"/>
                </svg>
                <div><strong>people</strong></div>
                <small class="text-muted">Personas</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/gear.svg') }}"/>
                </svg>
                <div><strong>gear</strong></div>
                <small class="text-muted">Configuración</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/shield.svg') }}"/>
                </svg>
                <div><strong>shield</strong></div>
                <small class="text-muted">Seguridad</small>
            </div>
        </div>

        <!-- Iconos de Datos y Análisis -->
        <div class="category-title">
            <h2>📊 Iconos de Datos y Análisis</h2>
        </div>
        <div class="icon-grid">
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/graph-up.svg') }}"/>
                </svg>
                <div><strong>graph-up</strong></div>
                <small class="text-muted">Gráfico ascendente</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/pie-chart.svg') }}"/>
                </svg>
                <div><strong>pie-chart</strong></div>
                <small class="text-muted">Gráfico circular</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/bar-chart.svg') }}"/>
                </svg>
                <div><strong>bar-chart</strong></div>
                <small class="text-muted">Gráfico de barras</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/speedometer2.svg') }}"/>
                </svg>
                <div><strong>speedometer2</strong></div>
                <small class="text-muted">Velocímetro</small>
            </div>
        </div>

        <!-- Iconos de Comunicación -->
        <div class="category-title">
            <h2>💬 Iconos de Comunicación</h2>
        </div>
        <div class="icon-grid">
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/chat.svg') }}"/>
                </svg>
                <div><strong>chat</strong></div>
                <small class="text-muted">Chat</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/envelope.svg') }}"/>
                </svg>
                <div><strong>envelope</strong></div>
                <small class="text-muted">Email</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/bell.svg') }}"/>
                </svg>
                <div><strong>bell</strong></div>
                <small class="text-muted">Notificación</small>
            </div>
            <div class="icon-demo">
                <svg class="bi" width="48" height="48" fill="currentColor">
                    <use href="{{ asset('vendor/blade-bootstrap-icons/megaphone.svg') }}"/>
                </svg>
                <div><strong>megaphone</strong></div>
                <small class="text-muted">Megáfono</small>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="alert alert-info">
                    <h4>ℹ️ Información del Sistema</h4>
                    <ul class="mb-0">
                        <li><strong>Total de iconos disponibles:</strong> 2,078 iconos SVG</li>
                        <li><strong>Ubicación:</strong> <code>public/vendor/blade-bootstrap-icons/</code></li>
                        <li><strong>Uso en Blade:</strong> <code>&lt;x-bs-icon name="icon-name" /&gt;</code></li>
                        <li><strong>Uso directo:</strong> <code>&lt;svg class="bi"&gt;&lt;use href="{{ asset('vendor/blade-bootstrap-icons/icon-name.svg') }}"/&gt;&lt;/svg&gt;</code></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Enlaces útiles -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="https://icons.getbootstrap.com/" target="_blank" class="btn btn-primary me-2">
                    🌐 Ver todos los iconos en Bootstrap Icons
                </a>
                <a href="/admin" class="btn btn-success">
                    🏠 Volver al Admin
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
