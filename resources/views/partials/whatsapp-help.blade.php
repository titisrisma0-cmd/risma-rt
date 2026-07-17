@php
    $whatsappNumber = preg_replace('/\D+/', '', (string) config('rw-smart.whatsapp_number'));
    if (str_starts_with($whatsappNumber, '0')) {
        $whatsappNumber = '62' . substr($whatsappNumber, 1);
    }
    $whatsappMessage = 'Halo, saya butuh bantuan terkait aplikasi RW SMART.';
    $whatsappUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode($whatsappMessage);
@endphp

@if($whatsappNumber)
    <a class="whatsapp-help"
       href="{{ $whatsappUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Butuh bantuan? Chat kami di WhatsApp"
       title="Butuh bantuan? Chat kami di WhatsApp">
        <span class="whatsapp-help__label">Butuh bantuan? Chat kami di WhatsApp</span>
        <span class="whatsapp-help__button" aria-hidden="true">
            <i class="fab fa-whatsapp"></i>
        </span>
    </a>

    @once
        <style>
            .whatsapp-help {
                position: fixed;
                right: 22px;
                bottom: 22px;
                z-index: 1085;
                display: flex;
                align-items: center;
                gap: 9px;
                text-decoration: none;
                font-family: Inter, system-ui, sans-serif;
            }
            .whatsapp-help__label {
                max-width: 230px;
                padding: 8px 12px;
                border: 1px solid var(--card-border, rgba(22, 101, 52, .18));
                border-radius: 999px;
                background: var(--surface, #f5f7f2);
                color: var(--text-primary, #1f2933);
                box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
                font-size: 12px;
                font-weight: 700;
                line-height: 1.25;
                opacity: 0;
                visibility: hidden;
                transform: translateX(8px);
                transition: opacity .2s ease, transform .2s ease, visibility .2s ease;
            }
            .whatsapp-help__button {
                width: 54px;
                height: 54px;
                flex: 0 0 54px;
                display: grid;
                place-items: center;
                border: 2px solid rgba(255, 255, 255, .82);
                border-radius: 50%;
                background: #128c3e;
                color: #fff;
                box-shadow: 0 9px 24px rgba(18, 140, 62, .38);
                font-size: 29px;
                transition: transform .2s ease, background-color .2s ease;
            }
            .whatsapp-help:hover .whatsapp-help__label,
            .whatsapp-help:focus-visible .whatsapp-help__label {
                opacity: 1;
                visibility: visible;
                transform: translateX(0);
            }
            .whatsapp-help:hover .whatsapp-help__button,
            .whatsapp-help:focus-visible .whatsapp-help__button {
                transform: translateY(-2px) scale(1.04);
                background: #0e7834;
            }
            .whatsapp-help:focus-visible { outline: none; }
            .whatsapp-help:focus-visible .whatsapp-help__button {
                outline: 3px solid #f6d860;
                outline-offset: 3px;
            }
            @media (max-width: 576px) {
                .whatsapp-help { right: 14px; bottom: 14px; }
                .whatsapp-help__button { width: 50px; height: 50px; flex-basis: 50px; font-size: 27px; }
                .whatsapp-help__label { display: none; }
            }
            @media (prefers-reduced-motion: reduce) {
                .whatsapp-help__label, .whatsapp-help__button { transition: none; }
            }
        </style>
    @endonce
@endif
