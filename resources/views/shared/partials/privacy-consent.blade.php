<div data-form-field="privacy_consent">
    <label class="flex cursor-pointer items-start gap-2 text_9 text-brand-gray-dark">
        <span class="relative mt-0.5 size-4 shrink-0 xs:size-6">
            <input type="checkbox" name="privacy_consent" value="1" required
                   class="peer pointer-events-none absolute inset-0 m-0 size-full opacity-0"
                   data-required-message="{{ __('validation.custom.privacy_consent.accepted') }}" />
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                 class="block size-full rounded-full text-brand-gray-dark peer-checked:text-brand-blue peer-focus-visible:ring-2 peer-focus-visible:ring-brand-blue peer-focus-visible:ring-offset-2">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 24C13.5759 24 15.1363 23.6896 16.5922 23.0866C18.0481 22.4835 19.371 21.5996 20.4853 20.4853C21.5996 19.371 22.4835 18.0481 23.0866 16.5922C23.6896 15.1363 24 13.5759 24 12C24 10.4241 23.6896 8.86371 23.0866 7.4078C22.4835 5.95189 21.5996 4.62902 20.4853 3.51472C19.371 2.40042 18.0481 1.5165 16.5922 0.913445C15.1363 0.310389 13.5759 -2.34822e-08 12 0C8.8174 4.74244e-08 5.76516 1.26428 3.51472 3.51472C1.26428 5.76515 0 8.8174 0 12C0 15.1826 1.26428 18.2348 3.51472 20.4853C5.76516 22.7357 8.8174 24 12 24ZM11.6907 16.8533L18.3573 8.85333L16.3093 7.14667L10.576 14.0253L7.60933 11.0573L5.724 12.9427L9.724 16.9427L10.756 17.9747L11.6907 16.8533Z" />
            </svg>
        </span>
        <span>
            Я согласен с <a href="{{ route('info.show', ['slug' => 'privacy-policy']) }}"
                            target="_blank" rel="noopener noreferrer"
                            class="text-brand-blue underline underline-offset-2">Политикой обработки персональных данных</a>.
        </span>
    </label>
</div>
