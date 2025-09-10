<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $faq->question }} - FAQ</title>
    <meta name="description" content="{{ Str::limit(strip_tags($faq->answer), 160) }}">
    <meta name="keywords" content="{{ implode(', ', $faq->keywords ?? []) }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('faq.index') }}">FAQs</a></li>
                        @if($faq->topic)
                            <li class="breadcrumb-item">{{ $faq->topic->name }}</li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($faq->question, 50) }}</li>
                    </ol>
                </nav>

                <!-- FAQ Content -->
                <article class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h1 class="h4 mb-2">{{ $faq->question }}</h1>
                                @if($faq->topic)
                                    <span class="badge bg-light text-dark">{{ $faq->topic->name }}</span>
                                @endif
                            </div>
                            <div class="text-end">
                                <small class="d-block">
                                    <i class="bi bi-eye"></i> {{ number_format($faq->view_count) }} vistas
                                </small>
                                @if($faq->is_featured)
                                    <span class="badge bg-warning text-dark mt-1">
                                        <i class="bi bi-star-fill"></i> Destacado
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="faq-answer">
                            {!! $faq->answer !!}
                        </div>
                        
                        @if($faq->short_answer)
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">Resumen:</h6>
                                <p class="mb-0">{{ $faq->short_answer }}</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-footer bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> 
                                    Actualizado: {{ $faq->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="markHelpful(true)">
                                        <i class="bi bi-hand-thumbs-up"></i> Útil
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="markHelpful(false)">
                                        <i class="bi bi-hand-thumbs-down"></i> No útil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Related FAQs -->
                @if($faq->topic && $faq->topic->faqs()->where('id', '!=', $faq->id)->where('is_draft', false)->count() > 0)
                    <div class="mt-5">
                        <h3 class="h5 mb-3">Preguntas relacionadas</h3>
                        <div class="list-group">
                            @foreach($faq->topic->faqs()->where('id', '!=', $faq->id)->where('is_draft', false)->limit(5)->get() as $relatedFaq)
                                <a href="{{ route('faq.show', $relatedFaq->slug) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $relatedFaq->question }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-eye"></i> {{ $relatedFaq->view_count }}
                                        </small>
                                    </div>
                                    @if($relatedFaq->short_answer)
                                        <p class="mb-1 text-muted">{{ Str::limit($relatedFaq->short_answer, 100) }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Back to FAQs -->
                <div class="mt-4 text-center">
                    <a href="{{ route('faq.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Volver a todas las FAQs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function markHelpful(isHelpful) {
            // Aquí podrías implementar la lógica para enviar el feedback
            // Por ahora solo mostramos un mensaje
            const message = isHelpful ? 'Gracias por tu feedback positivo' : 'Gracias por tu feedback';
            alert(message);
        }
    </script>
</body>
</html>
