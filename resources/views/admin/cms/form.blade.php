@extends('admin.layout')

@section('title', ($record->exists ? 'Edit ' : 'Add ') . $definition['singular'] . ' | CMS')

@section('admin-content')
    <header class="os-stack os-stack--tight">
        <a class="os-btn os-btn--ghost os-btn--sm" href="{{ route('admin.cms.index', $resource) }}">← Back to {{ $definition['label'] }}</a>
        <span class="os-label os-label--brand">// cms / {{ $resource }}</span>
        <h1 class="os-heading-lg">{{ $record->exists ? 'Edit ' . $definition['singular'] : 'Add ' . $definition['singular'] }}</h1>
    </header>

    <form
        method="POST"
        action="{{ $singleton
            ? route('admin.cms.update-singleton', $resource)
            : ($record->exists ? route('admin.cms.update', [$resource, $record->getKey()]) : route('admin.cms.store', $resource)) }}"
        class="os-card os-card__body os-stack"
    >
        @csrf
        @if ($singleton || $record->exists)
            @method('PUT')
        @endif

        @if ($resource === 'messages' && $record->exists)
            <section class="os-card os-card--inset os-card__body os-stack os-stack--tight" aria-label="Message details">
                <h2 class="os-heading-md">{{ $record->subjek ?: 'Contact message' }}</h2>
                <p class="os-text-sm os-text-secondary">From {{ $record->nama }} &lt;{{ $record->email }}&gt;</p>
                <p class="os-text-md">{{ $record->pesan }}</p>
            </section>
        @endif

        @foreach ($definition['fields'] as $name => $field)
            @php
                $value = old($name, $record->getAttribute($name));
                if ($field['type'] === 'list' && is_array($value)) {
                    $value = implode("\n", $value);
                }
                if ($field['type'] === 'datetime' && $value instanceof \Illuminate\Support\Carbon) {
                    $value = $value->format('Y-m-d\TH:i');
                }
            @endphp

            @if ($field['type'] === 'checkbox')
                <label class="os-row os-row--tight">
                    <input type="hidden" name="{{ $name }}" value="0" />
                    <input type="checkbox" name="{{ $name }}" value="1" @checked((bool) $value) />
                    <span class="os-field__label">{{ $field['label'] }}</span>
                </label>
            @else
                <label class="os-field @error($name) is-invalid @enderror">
                    <span class="os-field__label">{{ $field['label'] }}</span>

                    @if ($field['type'] === 'textarea' || $field['type'] === 'list')
                        <textarea
                            class="os-textarea"
                            name="{{ $name }}"
                            rows="{{ $field['type'] === 'list' ? 5 : 6 }}"
                            @required(in_array('required', $field['rules'] ?? [], true))
                        >{{ $value }}</textarea>
                        @if ($field['type'] === 'list')
                            <span class="os-field__help">One item per line.</span>
                        @endif
                    @elseif ($field['type'] === 'select')
                        <select class="os-select" name="{{ $name }}" @required(in_array('required', $field['rules'] ?? [], true))>
                            <option value="">Select {{ strtolower($field['label']) }}</option>
                            @foreach (($field['options'] ?? $options[$name] ?? []) as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @else
                        <input
                            class="os-input"
                            type="{{ $field['type'] }}"
                            name="{{ $name }}"
                            value="{{ $value instanceof \Illuminate\Support\Carbon ? ($field['type'] === 'datetime' ? $value->format('Y-m-d\TH:i') : $value->format('Y-m-d')) : $value }}"
                            @required(in_array('required', $field['rules'] ?? [], true))
                        />
                    @endif

                    @error($name)
                        <span class="os-field__error">{{ $message }}</span>
                    @enderror
                </label>
            @endif
        @endforeach

        <div class="os-row os-row--tight">
            <button class="os-btn os-btn--primary" type="submit">Save changes</button>
            <a class="os-btn os-btn--outline" href="{{ route('admin.cms.index', $resource) }}">Cancel</a>
        </div>
    </form>
@endsection
