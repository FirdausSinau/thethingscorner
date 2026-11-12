<fieldset>
    <legend class="font-display text-xl text-primary-dark">Metode pembayaran</legend>

    <div class="mt-4 space-y-2">
        @foreach ($paymentMethods as $method)
            <label class="flex items-center gap-2 text-sm">
                <input type="radio" name="payment_method" value="{{ $method->value }}" required
                       @checked(old('payment_method', 'manual_transfer') === $method->value)>
                {{ $method->label() }} (batas bayar {{ $method->reservationLabel() }})
            </label>
        @endforeach
    </div>

    @error('payment_method')
        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
    @enderror
</fieldset>
