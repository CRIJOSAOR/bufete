<x-filament-widgets::widget>
    <x-filament::section
        id="dashboard-agenda"
        heading="Agenda y pendientes"
        description="Vencimientos, tareas y audiencias que requieren atención."
        icon="heroicon-o-calendar-days"
    >
        <div class="dashboard-agenda-grid">
            @foreach ([
                [
                    'title' => 'Tareas vencidas',
                    'description' => 'Requieren seguimiento',
                    'items' => $overdueTasks,
                    'empty' => 'No tienes tareas vencidas.',
                    'tone' => 'danger',
                    'type' => 'task',
                ],
                [
                    'title' => 'Tareas próximas',
                    'description' => 'Para hoy y los próximos 7 días',
                    'items' => $upcomingTasks,
                    'empty' => 'No hay tareas con fecha para esta semana.',
                    'tone' => 'primary',
                    'type' => 'task',
                ],
                [
                    'title' => 'Audiencias próximas',
                    'description' => 'En los próximos 7 días',
                    'items' => $upcomingHearings,
                    'empty' => 'No hay audiencias programadas esta semana.',
                    'tone' => 'warning',
                    'type' => 'hearing',
                ],
            ] as $group)
                <section class="dashboard-agenda-group">
                    <header class="dashboard-agenda-group-header">
                        <div class="dashboard-agenda-group-title">
                            <span @class([
                                'dashboard-agenda-dot',
                                'dashboard-agenda-dot-danger' => $group['tone'] === 'danger',
                                'dashboard-agenda-dot-primary' => $group['tone'] === 'primary',
                                'dashboard-agenda-dot-warning' => $group['tone'] === 'warning',
                            ])></span>
                            <div>
                                <h3 class="dashboard-agenda-group-name">{{ $group['title'] }}</h3>
                                <p class="dashboard-agenda-group-description">{{ $group['description'] }}</p>
                            </div>
                        </div>
                        <span @class([
                            'dashboard-agenda-count',
                            'dashboard-agenda-count-danger' => $group['tone'] === 'danger',
                            'dashboard-agenda-count-primary' => $group['tone'] === 'primary',
                            'dashboard-agenda-count-warning' => $group['tone'] === 'warning',
                        ])>
                            {{ $group['items']->count() }}
                        </span>
                    </header>

                    @if ($group['items']->isNotEmpty())
                        <ul class="dashboard-agenda-list">
                            @foreach ($group['items'] as $item)
                                @php
                                    $caseUrl = \App\Filament\Resources\Casos\CasoResource::canEdit($item->caso)
                                        ? \App\Filament\Resources\Casos\CasoResource::getUrl('edit', ['record' => $item->caso])
                                        : null;
                                    $date = $group['type'] === 'task' ? $item->fecha_limite : $item->fecha_hora;
                                @endphp
                                <li class="dashboard-agenda-item">
                                    <time @class([
                                        'dashboard-agenda-date',
                                        'dashboard-agenda-date-danger' => $group['tone'] === 'danger',
                                        'dashboard-agenda-date-primary' => $group['tone'] === 'primary',
                                        'dashboard-agenda-date-warning' => $group['tone'] === 'warning',
                                    ])>
                                        <span class="dashboard-agenda-date-day">{{ $date->format('d') }}</span>
                                        <span class="dashboard-agenda-date-month">{{ $date->translatedFormat('M') }}</span>
                                    </time>

                                    <div class="dashboard-agenda-item-content">
                                        @if ($caseUrl)
                                            <a href="{{ $caseUrl }}" class="dashboard-agenda-item-title">
                                                {{ $item->titulo }}
                                            </a>
                                        @else
                                            <p class="dashboard-agenda-item-title">{{ $item->titulo }}</p>
                                        @endif
                                        <p class="dashboard-agenda-item-case">
                                            {{ $item->caso->numero }} <span aria-hidden="true">·</span> {{ $item->caso->titulo }}
                                        </p>
                                    </div>

                                    <span class="dashboard-agenda-item-time">
                                        {{ $date->format('H:i') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="dashboard-agenda-empty">
                            <span class="dashboard-agenda-empty-icon">
                                <x-filament::icon
                                    :icon="$group['type'] === 'hearing' ? 'heroicon-o-calendar-days' : 'heroicon-o-check-circle'"
                                />
                            </span>
                            <p>{{ $group['empty'] }}</p>
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
