<div class="inquiry-form">
    @if($sent)
        <div class="notice" role="status" tabindex="-1">{{ __("Thank you for contacting Juanovio. Your inquiry has been received. We will review the information and respond when appropriate.") }}</div>
    @else
        @if(!$this->available())<div class="notice" role="status">{{ __("Our online inquiry form is being prepared. Submissions are currently unavailable.") }}</div>@endif
        @error('submission')<div class="notice" role="alert">{{ $message }}</div>@enderror
        <form wire:submit="submit">
            @csrf
            <fieldset @disabled(!$this->available())>
                <legend class="sr-only">{{ __("Commercial inquiry details") }}</legend>
                <div class="form-grid">
                    <div class="form-full"><flux:select wire:model.live="type" :label="__('Inquiry type')" required>@foreach(config('juanovio.types') as $value => $label)<flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>@endforeach</flux:select></div>
                    <flux:input wire:model="name" :label="__('Full name')" autocomplete="name" required maxlength="120" />
                    <flux:input wire:model="company" :label="__('Company')" autocomplete="organization" required maxlength="160" />
                    <flux:input wire:model="email" :label="__('Business email')" type="email" autocomplete="email" required maxlength="254" />
                    <flux:input wire:model="country" :label="__('Country')" autocomplete="country-name" required maxlength="100" />
                    <div class="form-full"><flux:select wire:model="category" :label="__('Category of interest')" :placeholder="__('Select a category')" :required="in_array($type, ['buy', 'supply'])">@foreach(config('juanovio.categories') as $value => $category)<flux:select.option value="{{ $value }}">{{ $category['title'] }}</flux:select.option>@endforeach<flux:select.option value="unsure">{{ __("Not sure yet") }}</flux:select.option></flux:select></div>
                    <div class="form-full"><flux:input wire:model="product" :label="__('Product / specifications')" :required="in_array($type, ['buy', 'supply'])" maxlength="200" /></div>
                    <flux:input wire:model="quantity" :label="__('Estimated quantity (optional)')" type="number" min="0" step="any" max="999999999999" />
                    <flux:select wire:model.live="quantityUnit" :label="__('Quantity unit (optional)')"><flux:select.option value="">{{ __("Select a unit") }}</flux:select.option>@foreach(config('juanovio.quantity_units') as $value => $label)<flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>@endforeach</flux:select>
                    @if(in_array($quantityUnit, ['bags', 'other']))
                        <div class="form-full"><flux:input wire:model="packDetails" :label="__('Pack size / unit details (optional)')" :placeholder="__('e.g. 42.5 kg per bag')" maxlength="200" /></div>
                    @endif
                    <div class="form-full location-fields">
                        <div class="form-grid">
                            <flux:input wire:model="origin" :label="__('Origin / sourcing location (optional)')" :placeholder="__('Country, city or port, if known')" maxlength="200" aria-describedby="location-guidance" />
                            <flux:input wire:model="destination" :label="__('Destination / delivery location (optional)')" :placeholder="__('Country, city or port, if known')" maxlength="200" aria-describedby="location-guidance" />
                        </div>
                        <p id="location-guidance" class="form-note">{{ __("Complete either or both locations. Leave blank if the location is still open.") }}</p>
                    </div>
                    <div class="form-full"><flux:textarea wire:model="message" :label="__('Message')" rows="6" required minlength="20" maxlength="5000" :description="__('Please share requirements, specifications, and timing. Do not include sensitive financial information.')" /></div>
                    <div class="honeypot" aria-hidden="true"><label for="website">{{ __("Leave this blank") }}</label><input id="website" wire:model="website" tabindex="-1" autocomplete="off"></div>
                    <div class="form-full"><flux:checkbox wire:model="consent" :label="__('I acknowledge the privacy policy and the use of my information to review and respond to this inquiry.')" required /></div>
                </div>
                <p class="form-note">{{ __("Your information will be used to review and respond to your inquiry. ") }}<a href="{{ \App\Support\Locale::url('privacy') }}">{{ __("Read the privacy policy") }}</a>.</p>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">{{ __("Send Inquiry") }}</flux:button>
            </fieldset>
        </form>
    @endif
</div>
