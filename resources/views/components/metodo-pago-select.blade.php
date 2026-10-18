@props(['metodos', 'selected' => null, 'placeholder' => 'Sin método'])

<select name="metodo_pago_id" {{ $attributes }}>
    <option value="">{{ $placeholder }}</option>
    @foreach($metodos as $metodo)
        @if($metodo->activo || (int) $selected === $metodo->id)
            <option value="{{ $metodo->id }}" @selected((int) $selected === $metodo->id)>{{ $metodo->nombre }}{{ $metodo->activo ? '' : ' (inactivo)' }}</option>
        @endif
    @endforeach
</select>
