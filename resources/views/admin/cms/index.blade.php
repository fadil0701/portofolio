@extends('admin.layout')

@section('title', $definition['label'] . ' | CMS')

@section('admin-content')
    <header class="os-row os-row--between">
        <div class="os-stack os-stack--tight">
            <span class="os-label os-label--brand">// cms / {{ $resource }}</span>
            <h1 class="os-heading-lg">{{ $definition['label'] }}</h1>
        </div>

        @if ($definition['singleton'])
            <a class="os-btn os-btn--primary" href="{{ route('admin.cms.edit-singleton', $resource) }}">Edit {{ $definition['label'] }}</a>
        @elseif ($definition['create'])
            <a class="os-btn os-btn--primary" href="{{ route('admin.cms.create', $resource) }}">Add {{ $definition['singular'] }}</a>
        @endif
    </header>

    <section class="os-card">
        @if ($records->isNotEmpty())
            <div class="os-table-wrap">
                <div class="os-table-scroll">
                    <table class="os-table">
                        <thead>
                            <tr>
                                @foreach ($definition['columns'] as $column)
                                    <th>{{ $definition['fields'][$column]['label'] ?? str($column)->replace('_', ' ')->title() }}</th>
                                @endforeach
                                <th class="os-table__actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $record)
                                <tr>
                                    @foreach ($definition['columns'] as $column)
                                        @php($value = $record->getAttribute($column))
                                        <td>
                                            @if (is_array($value))
                                                {{ implode(', ', $value) }}
                                            @elseif (is_bool($value))
                                                {{ $value ? 'Yes' : 'No' }}
                                            @elseif ($value instanceof \Illuminate\Support\Carbon)
                                                {{ $value->format('Y-m-d H:i') }}
                                            @else
                                                {{ $value ?: '—' }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="os-table__actions">
                                        <a class="os-btn os-btn--ghost os-btn--sm" href="{{ route('admin.cms.edit', [$resource, $record->getKey()]) }}">Edit</a>
                                        @if ($definition['delete'])
                                            <form method="POST" action="{{ route('admin.cms.destroy', [$resource, $record->getKey()]) }}" class="os-inline-form">
                                                @csrf
                                                @method('DELETE')
                                                <button class="os-btn os-btn--danger-quiet os-btn--sm" type="submit">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="os-card__body">{{ $records->links() }}</div>
        @else
            <div class="os-card__body os-stack">
                <p class="os-text-secondary">No {{ strtolower($definition['label']) }} found.</p>
                @if ($definition['create'])
                    <a class="os-btn os-btn--primary" href="{{ route('admin.cms.create', $resource) }}">Add {{ $definition['singular'] }}</a>
                @elseif ($definition['singleton'])
                    <a class="os-btn os-btn--primary" href="{{ route('admin.cms.edit-singleton', $resource) }}">Set up {{ $definition['label'] }}</a>
                @endif
            </div>
        @endif
    </section>
@endsection
