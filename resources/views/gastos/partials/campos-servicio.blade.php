@php
    $valor = fn (string $campo, mixed $porDefecto = null) => $conValoresAnteriores ? old($campo, $porDefecto) : $porDefecto;
    $error = fn (string $campo) => $conValoresAnteriores ? $errors->first($campo) : null;
@endphp

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-12">
    <x-ui.field label="Servicio" :for="$prefijo.'-servicio'" :error="$error('servicio')" class="sm:col-span-2 lg:col-span-4">
        <x-ui.input type="text" name="servicio" :id="$prefijo.'-servicio'" required maxlength="255" placeholder="Ej: Netflix"
            :value="$valor('servicio', $gasto?->servicio)" :invalid="(bool) $error('servicio')" />
    </x-ui.field>

    <x-ui.field label="Día de pago" :for="$prefijo.'-dia_pago'" :error="$error('dia_pago')" class="lg:col-span-2">
        <x-ui.input type="number" name="dia_pago" :id="$prefijo.'-dia_pago'" required min="1" max="31" placeholder="1-31"
            :value="$valor('dia_pago', $gasto?->dia_pago)" :invalid="(bool) $error('dia_pago')" />
    </x-ui.field>

    <x-ui.field label="Monto (USD)" :for="$prefijo.'-monto'" :error="$error('monto')" class="lg:col-span-2">
        <x-ui.input type="number" name="monto" :id="$prefijo.'-monto'" step="0.01" min="0" placeholder="0.00"
            :value="$valor('monto', $gasto?->monto)" :invalid="(bool) $error('monto')" />
    </x-ui.field>

    <x-ui.field label="Se paga en" :for="$prefijo.'-moneda'" :error="$error('moneda')" class="lg:col-span-2">
        @php($moneda = $valor('moneda', $gasto?->moneda ?? 'USD'))
        <x-ui.select name="moneda" :id="$prefijo.'-moneda'">
            <option value="USD" @selected($moneda !== 'VES')>Dólares</option>
            <option value="VES" @selected($moneda === 'VES')>Bolívares (BCV)</option>
        </x-ui.select>
    </x-ui.field>

    <x-ui.field label="Método de pago" :for="$prefijo.'-metodo_pago_id'" :error="$error('metodo_pago_id')" class="lg:col-span-2">
        <x-metodo-pago-select :metodos="$metodosPago" :selected="$valor('metodo_pago_id', $gasto?->metodo_pago_id)" :id="$prefijo.'-metodo_pago_id'" />
    </x-ui.field>
</div>
