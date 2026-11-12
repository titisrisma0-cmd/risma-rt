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
            <span class="whatsapp-help__pulse"></span>
            <i class="fab fa-whatsapp"></i>
        </span>
    </a>

    @once
        <style>
            .whatsapp-help {
                position: fixed;
                right: 24px;
                bottom: 24px;
                z-index: 1085;
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
                font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            }
            .whatsapp-help__label {
                max-width: 240px;
                padding: 8px 14px;
                border: 1px solid var(--border, #dbe9e0);
                border-radius: 9999px;
                background: var(--surface, #ffffff);
                color: var(--ink, #0f2a1d);
                box-shadow: 0 8px 24px rgba(15, 42, 29, 0.12);
                font-size: 13px;
                font-weight: 700;
                line-height: 1.3;
                opacity: 0;
                visibility: hidden;
                transform: translateX(8px);
                transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
            }
            .whatsapp-help__button {
                width: 54px;
                height: 54px;
                flex: 0 0 54px;
                display: grid;
                place-items: center;
                border: 2px solid rgba(255, 255, 255, 0.9);
                border-radius: 50%;
                background: var(--primary, #0e7a4b);
                color: #ffffff;
                box-shadow: 0 10px 24px rgba(14, 122, 75, 0.38);
                font-size: 28px;
                position: relative;
                transition: transform 0.2s ease, background-color 0.2s ease;
            }
            /* Cincin Pulsa (Pulse Ring) */
            .whatsapp-help__pulse {
                position: absolute;
                inset: -6px;
                border-radius: 50%;
                border: 2.5px solid var(--primary, #0e7a4b);
                pointer-events: none;
                animation: ring 2.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
            }
            .whatsapp-help:hover .whatsapp-help__label,
            .whatsapp-help:focus-visible .whatsapp-help__label {
                opacity: 1;
                visibility: visible;
                transform: translateX(0);
            }
            .whatsapp-help:hover .whatsapp-help__button,
            .whatsapp-help:focus-visible .whatsapp-help__button {
                transform: translateY(-2px) scale(1.05);
                background: var(--primary-dark, #0a5c38);
            }
            .whatsapp-help:focus-visible { outline: none; }
            .whatsapp-help:focus-visible .whatsapp-help__button {
                outline: 3px solid var(--accent, #f4a62a);
                outline-offset: 3px;
            }
            @media (max-width: 576px) {
                .whatsapp-help { right: 16px; bottom: 16px; }
                .whatsapp-help__button { width: 48px; height: 48px; flex-basis: 48px; font-size: 25px; }
                .whatsapp-help__label { display: none; }
            }
            @media (prefers-reduced-motion: reduce) {
                .whatsapp-help__label, .whatsapp-help__button, .whatsapp-help__pulse {
                    transition: none !important;
                    animation: none !important;
                }
            }
            @media print {
                .whatsapp-help { display: none !important; }
            }
        </style>
    @endonce
@endif
