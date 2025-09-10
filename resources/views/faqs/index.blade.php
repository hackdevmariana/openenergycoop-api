<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas Frecuentes - FAQ</title>
    <meta name="description" content="Encuentra respuestas a las preguntas más frecuentes sobre nuestra plataforma de energía cooperativa.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Header -->
                <div class="text-center mb-5">
                    <h1 class="display-4 text-primary">
                        <i class="bi bi-question-circle"></i>
                        Preguntas Frecuentes
                    </h1>
                    <p class="lead text-muted">
                        Encuentra respuestas a las preguntas más comunes sobre nuestra plataforma
                    </p>
                </div>

                <!-- Search Form -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('faq.index') }}" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           placeholder="Buscar en preguntas frecuentes..."
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Results Info -->
                @if(request('search'))
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Mostrando resultados para: <strong>"{{ request('search') }}"</strong>
                        <a href="{{ route('faq.index') }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-x-circle"></i> Limpiar búsqueda
                        </a>
                    </div>
                @endif

                <!-- FAQs List -->
                @if($faqs->count() > 0)
                    <div class="accordion" id="faqsAccordion">
                        @foreach($faqs as $index => $faq)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $faq->id }}">
                                    <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse{{ $faq->id }}" 
                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                            aria-controls="collapse{{ $faq->id }}">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $faq->question }}</strong>
                                                @if($faq->topic)
                                                    <span class="badge bg-primary ms-2">{{ $faq->topic->name }}</span>
                                                @endif
                                            </div>
                                            <div class="text-end me-3">
                                                @if($faq->is_featured)
                                                    <i class="bi bi-star-fill text-warning" title="Destacado"></i>
                                                @endif
                                                <small class="text-muted">
                                                    <i class="bi bi-eye"></i> {{ $faq->view_count }}
                                                </small>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $faq->id }}" 
                                     class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                     aria-labelledby="heading{{ $faq->id }}" 
                                     data-bs-parent="#faqsAccordion">
                                    <div class="accordion-body">
                                        <div class="faq-answer">
                                            {!! $faq->answer !!}
                                        </div>
                                        
                                        @if($faq->short_answer)
                                            <div class="mt-3 p-3 bg-light rounded">
                                                <h6 class="text-muted mb-2">Resumen:</h6>
                                                <p class="mb-0">{{ $faq->short_answer }}</p>
                                            </div>
                                        @endif
                                        
                                        <div class="mt-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <a href="{{ route('faq.show', $faq->slug) }}" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> Ver página completa
                                                </a>
                                            </div>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-success btn-sm" onclick="markHelpful({{ $faq->id }}, true)">
                                                    <i class="bi bi-hand-thumbs-up"></i> Útil
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="markHelpful({{ $faq->id }}, false)">
                                                    <i class="bi bi-hand-thumbs-down"></i> No útil
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($faqs->hasPages())
                        <div class="mt-4">
                            {{ $faqs->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-question-circle display-1 text-muted"></i>
                        <h3 class="mt-3">No se encontraron FAQs</h3>
                        <p class="text-muted">
                            @if(request('search'))
                                No hay resultados para tu búsqueda. Intenta con otros términos.
                            @else
                                No hay preguntas frecuentes disponibles en este momento.
                            @endif
                        </p>
                        @if(request('search'))
                            <a href="{{ route('faq.index') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Ver todas las FAQs
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function markHelpful(faqId, isHelpful) {
            // Aquí podrías implementar la lógica para enviar el feedback
            // Por ahora solo mostramos un mensaje
            const message = isHelpful ? 'Gracias por tu feedback positivo' : 'Gracias por tu feedback';
            alert(message);
        }
    </script>
</body>
</html>
