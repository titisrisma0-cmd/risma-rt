@php
    $whatsappNumber = \App\Services\AssistantService::getWhatsAppNumber();
    $whatsappUrl = \App\Services\AssistantService::getWhatsAppUrl();
    $currentUser = auth()->user();
    if ($currentUser) {
        $currentUser->loadMissing(['profile.wilayahRt', 'role']);
        $chatFirstName = \App\Services\AssistantService::getFirstName(optional($currentUser->profile)->nama_lengkap ?? $currentUser->name);
        $chatGreeting = "Halo, {$chatFirstName}! Saya Asisten Smart RW. Ada yang bisa saya bantu?";
        $isLoggedIn = true;
    } else {
        $chatFirstName = null;
        $chatGreeting = "Halo! Saya Asisten Smart RW. Masuk dulu supaya saya bisa membantu lebih personal.";
        $isLoggedIn = false;
    }
    $roleChips = \App\Services\AssistantService::getSuggestionsForRole($currentUser);
@endphp

@if($whatsappNumber)
    <div class="whatsapp-help-container"
         id="waHelpContainer"
         data-current-user-id="{{ auth()->id() ?? 'guest' }}"
         data-is-logged-in="{{ $isLoggedIn ? '1' : '0' }}"
         data-user-name="{{ $chatFirstName ?? '' }}"
         data-login-url="{{ route('login') }}">
        {{-- Popover Menu Pilihan Bantuan --}}
        <div class="whatsapp-popover-menu"
             id="waHelpMenu"
             role="menu"
             aria-label="Pilihan Bantuan Smart RW"
             aria-hidden="true">
            <div class="wa-popover-header">
                <i class="fas fa-headset text-primary me-1.5"></i>
                <span>Pusat Bantuan RW</span>
            </div>

            {{-- Item 1: WhatsApp CS --}}
            <a href="{{ $whatsappUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               role="menuitem"
               tabindex="-1"
               class="wa-menu-item"
               id="menuItemWaCs">
                <div class="wa-menu-icon wa-menu-icon-wa" aria-hidden="true">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div class="wa-menu-text">
                    <strong class="wa-menu-title">WhatsApp CS</strong>
                    <span class="wa-menu-desc">Respons cepat</span>
                </div>
                <i class="fas fa-chevron-right wa-menu-arrow" aria-hidden="true"></i>
            </a>

            {{-- Item 2: Live Chat AI --}}
            <button type="button"
                    role="menuitem"
                    tabindex="-1"
                    class="wa-menu-item"
                    id="menuItemLiveChat">
                <div class="wa-menu-icon wa-menu-icon-ai" aria-hidden="true">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="wa-menu-text">
                    <strong class="wa-menu-title">Live Chat AI</strong>
                    <span class="wa-menu-desc">Asisten otomatis, 24 jam</span>
                </div>
                <i class="fas fa-chevron-right wa-menu-arrow" aria-hidden="true"></i>
            </button>
        </div>

        {{-- Tombol Utama Melayang (Ikon Headset Bantuan) --}}
        <button type="button"
                class="whatsapp-help"
                id="btnWaToggle"
                aria-haspopup="menu"
                aria-expanded="false"
                aria-controls="waHelpMenu"
                aria-label="Buka menu bantuan"
                title="Buka menu bantuan">
            <span class="whatsapp-help__label">Chat & Bantuan</span>
            <span class="whatsapp-help__button" aria-hidden="true">
                <span class="whatsapp-help__pulse"></span>
                <span class="whatsapp-help__icon-wrap">
                    <i class="fas fa-headset wa-icon-main"></i>
                    <i class="fas fa-xmark wa-icon-close"></i>
                </span>
            </span>
        </button>

        {{-- Jendela Chat Asisten Smart RW --}}
        <div class="ai-chat-window"
             id="aiChatWindow"
             role="dialog"
             aria-modal="false"
             aria-label="Jendela Chat Asisten Smart RW"
             style="display: none;">
            {{-- Header Chat --}}
            <div class="ai-chat-header">
                <div class="ai-chat-header-info">
                    <div class="ai-chat-avatar" aria-hidden="true">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h6 class="ai-chat-title mb-0">Asisten Smart RW</h6>
                        <span class="ai-chat-status">
                            <span class="ai-status-dot"></span> Online 24 Jam
                        </span>
                    </div>
                </div>
                <div class="ai-chat-header-actions">
                    <a href="{{ $whatsappUrl }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn-wa-header"
                       title="Hubungi pengurus via WhatsApp">
                        <i class="fab fa-whatsapp"></i> <span>CS</span>
                    </a>
                    <button type="button"
                            class="btn-chat-close"
                            id="btnCloseAiChat"
                            aria-label="Tutup jendela chat"
                            title="Tutup">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            </div>

            {{-- Catatan Kecil / Disclaimer --}}
            <div class="ai-chat-disclaimer">
                <i class="fas fa-circle-info text-primary me-1"></i>
                <span>Asisten otomatis, jawaban bisa kurang tepat.</span>
            </div>

            {{-- Isi Percakapan --}}
            <div class="ai-chat-body" id="aiChatBody">
                {{-- Pesan Pembuka Asisten --}}
                <div class="chat-msg chat-msg-bot" id="initialBotGreeting">
                    <div class="chat-bubble">
                        <span>{{ $chatGreeting }}</span>
                        @if(!$isLoggedIn)
                            <div class="mt-2 pt-1 border-top border-light-subtle">
                                <a href="{{ route('login') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold text-white shadow-sm" style="font-size: 0.78rem;">
                                    <i class="fas fa-right-to-bracket me-1"></i> Masuk
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="chat-time">{{ now()->format('H:i') }}</div>
                </div>

                {{-- Saran Pertanyaan Cepat --}}
                <div class="ai-chat-suggestions" id="aiChatSuggestions">
                    <div class="suggestions-label">Saran pertanyaan cepat:</div>
                    <div class="suggestions-chips" id="suggestionsChipsContainer">
                        @foreach($roleChips as $chipText)
                            <button type="button" class="chip-btn" data-query="{{ $chipText }}">
                                <i class="fas fa-circle-question me-1 opacity-75"></i> {{ $chipText }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Form Input Pesan --}}
            <form class="ai-chat-footer" id="aiChatForm">
                <input type="text"
                       id="aiChatInput"
                       class="ai-chat-input"
                       placeholder="Ketik pertanyaan Anda..."
                       maxlength="300"
                       autocomplete="off"
                       aria-label="Ketik pertanyaan untuk Asisten Smart RW">
                <button type="submit" class="ai-chat-send" id="btnSendAiChat" aria-label="Kirim pesan">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

    @once
        <style>
            /* Container Pembungkus */
            .whatsapp-help-container {
                position: fixed;
                right: 24px;
                bottom: max(24px, calc(env(safe-area-inset-bottom, 0px) + 20px));
                z-index: 1050;
                font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            }

            /* Tombol WhatsApp Utama */
            .whatsapp-help {
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
                background: none;
                border: none;
                padding: 0;
                cursor: pointer;
                outline: none;
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
                white-space: nowrap;
            }
            .whatsapp-help__button {
                width: 54px;
                height: 54px;
                flex: 0 0 54px;
                display: grid;
                place-items: center;
                border: 2px solid rgba(255, 255, 255, 0.95);
                border-radius: 50%;
                background: var(--primary, #0e7a4b);
                color: #ffffff;
                box-shadow: 0 10px 24px rgba(14, 122, 75, 0.38);
                font-size: 28px;
                position: relative;
                transition: transform 0.2s ease, background-color 0.2s ease;
            }
            @keyframes waRing {
                0% { transform: scale(1); opacity: 0.5; }
                100% { transform: scale(1.35); opacity: 0; }
            }
            .whatsapp-help__pulse {
                position: absolute;
                inset: -4px;
                border-radius: 50%;
                border: 2px solid var(--primary, #0e7a4b);
                pointer-events: none;
                animation: waRing 2.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
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
            .whatsapp-help:focus-visible .whatsapp-help__button {
                outline: 3px solid var(--accent, #f4a62a);
                outline-offset: 3px;
            }

            /* Ikon Headset ke Tanda X Halus (~200ms) */
            .whatsapp-help__icon-wrap {
                position: relative;
                width: 100%;
                height: 100%;
                display: grid;
                place-items: center;
            }
            .wa-icon-main,
            .wa-icon-close {
                position: absolute;
                display: grid;
                place-items: center;
                font-size: 26px;
                transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .wa-icon-main {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }
            .wa-icon-close {
                opacity: 0;
                transform: rotate(-90deg) scale(0.6);
            }
            /* Saat popover menu terbuka */
            .whatsapp-help.is-open .wa-icon-main {
                opacity: 0;
                transform: rotate(90deg) scale(0.6);
            }
            .whatsapp-help.is-open .wa-icon-close {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }

            /* ==================== POPOVER MENU ==================== */
            .whatsapp-popover-menu {
                position: absolute;
                bottom: calc(100% + 14px);
                right: 0;
                width: 270px;
                background: var(--surface, #ffffff);
                border: 1px solid var(--border, #dbe9e0);
                border-radius: 18px;
                box-shadow: 0 16px 36px rgba(15, 42, 29, 0.14), 0 4px 12px rgba(15, 42, 29, 0.06);
                padding: 10px;
                opacity: 0;
                visibility: hidden;
                transform: translateY(8px);
                transition: opacity 0.2s ease, transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.2s ease;
                pointer-events: none;
                z-index: 1060;
            }
            .whatsapp-popover-menu.active {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
                pointer-events: auto;
            }
            .wa-popover-header {
                font-size: 0.76rem;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: var(--ink-soft, #4b6355);
                padding: 6px 10px 8px 10px;
                border-bottom: 1px solid var(--border, #dbe9e0);
                margin-bottom: 6px;
                display: flex;
                align-items: center;
            }
            .wa-menu-item {
                display: flex;
                align-items: center;
                gap: 12px;
                width: 100%;
                padding: 10px 12px;
                border: none;
                border-radius: 12px;
                background: transparent;
                text-decoration: none;
                color: var(--ink, #0f2a1d);
                cursor: pointer;
                transition: background-color 0.16s ease, transform 0.16s ease;
                text-align: left;
                outline: none;
            }
            .wa-menu-item:hover,
            .wa-menu-item:focus-visible {
                background: var(--surface-2, #f0f5f2);
                transform: translateX(-2px);
            }
            .wa-menu-item:focus-visible {
                outline: 2px solid var(--primary, #0e7a4b);
            }
            .wa-menu-icon {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: grid;
                place-items: center;
                font-size: 1.15rem;
                flex-shrink: 0;
            }
            .wa-menu-icon-wa {
                background: rgba(37, 211, 102, 0.15);
                color: #128c7e;
            }
            .wa-menu-icon-ai {
                background: rgba(14, 122, 75, 0.15);
                color: var(--primary, #0e7a4b);
            }
            .wa-menu-text {
                flex-grow: 1;
                display: flex;
                flex-direction: column;
                gap: 2px;
            }
            .wa-menu-title {
                font-size: 0.92rem;
                font-weight: 700;
                color: var(--ink, #0f2a1d);
                line-height: 1.2;
            }
            .wa-menu-desc {
                font-size: 0.78rem;
                color: var(--ink-soft, #6f8578);
                line-height: 1.2;
            }
            .wa-menu-arrow {
                font-size: 0.75rem;
                color: var(--ink-soft, #a0b2a7);
                transition: transform 0.15s ease;
            }
            .wa-menu-item:hover .wa-menu-arrow {
                transform: translateX(3px);
                color: var(--primary, #0e7a4b);
            }

            /* ==================== JENDELA CHAT ASISTEN ==================== */
            .ai-chat-window {
                position: fixed;
                right: 24px;
                bottom: 92px;
                width: 380px;
                max-width: calc(100vw - 32px);
                height: 520px;
                max-height: calc(100vh - 120px);
                background: var(--surface, #ffffff);
                border: 1px solid var(--border, #dbe9e0);
                border-radius: 20px;
                box-shadow: 0 20px 48px rgba(15, 42, 29, 0.2), 0 8px 16px rgba(15, 42, 29, 0.08);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                z-index: 1070;
                animation: chatOpen 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            }
            @keyframes chatOpen {
                from { opacity: 0; transform: translateY(12px) scale(0.97); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            .ai-chat-header {
                background: linear-gradient(135deg, #0e7a4b, #0a5c38);
                color: #ffffff;
                padding: 14px 16px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-shrink: 0;
            }
            .ai-chat-header-info {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .ai-chat-avatar {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                background: rgba(255, 255, 255, 0.2);
                display: grid;
                place-items: center;
                font-size: 1.1rem;
                color: #ffffff;
            }
            .ai-chat-title {
                font-size: 0.98rem;
                font-weight: 700;
                letter-spacing: -0.01em;
            }
            .ai-chat-status {
                font-size: 0.72rem;
                opacity: 0.9;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .ai-status-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #4ade80;
                display: inline-block;
                box-shadow: 0 0 6px rgba(74, 222, 128, 0.8);
            }
            .ai-chat-header-actions {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .btn-wa-header {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: #ffffff;
                padding: 5px 9px;
                border-radius: 8px;
                font-size: 0.76rem;
                font-weight: 700;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 4px;
                transition: background 0.15s ease;
            }
            .btn-wa-header:hover {
                background: rgba(255, 255, 255, 0.35);
                color: #ffffff;
            }
            .btn-chat-close {
                background: none;
                border: none;
                color: #ffffff;
                font-size: 1.15rem;
                width: 32px;
                height: 32px;
                border-radius: 8px;
                display: grid;
                place-items: center;
                cursor: pointer;
                opacity: 0.85;
                transition: opacity 0.15s ease, background 0.15s ease;
            }
            .btn-chat-close:hover {
                opacity: 1;
                background: rgba(255, 255, 255, 0.2);
            }

            /* Disclaimer */
            .ai-chat-disclaimer {
                background: var(--surface-2, #f0f5f2);
                border-bottom: 1px solid var(--border, #dbe9e0);
                padding: 6px 14px;
                font-size: 0.74rem;
                color: var(--ink-soft, #556c5f);
                display: flex;
                align-items: center;
                flex-shrink: 0;
            }

            /* Body Percakapan */
            .ai-chat-body {
                flex-grow: 1;
                padding: 16px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 12px;
                background: var(--bg, #f7faf8);
            }
            .chat-msg {
                display: flex;
                flex-direction: column;
                max-width: 86%;
            }
            .chat-msg-bot {
                align-self: flex-start;
            }
            .chat-msg-user {
                align-self: flex-end;
            }
            .chat-bubble {
                padding: 10px 14px;
                border-radius: 14px;
                font-size: 0.88rem;
                line-height: 1.45;
                word-wrap: break-word;
                white-space: pre-line;
            }
            .chat-msg-bot .chat-bubble {
                background: var(--surface, #ffffff);
                color: var(--ink, #0f2a1d);
                border: 1px solid var(--border, #dbe9e0);
                border-top-left-radius: 3px;
                box-shadow: 0 2px 6px rgba(15, 42, 29, 0.04);
            }
            .chat-msg-user .chat-bubble {
                background: var(--primary, #0e7a4b);
                color: #ffffff;
                border-top-right-radius: 3px;
            }
            .chat-time {
                font-size: 0.68rem;
                color: var(--ink-soft, #83978c);
                margin-top: 3px;
                padding: 0 4px;
            }
            .chat-msg-user .chat-time {
                text-align: right;
            }

            /* Chips Saran Pertanyaan */
            .ai-chat-suggestions {
                margin-top: 4px;
            }
            .suggestions-label {
                font-size: 0.74rem;
                font-weight: 700;
                color: var(--ink-soft, #6f8578);
                margin-bottom: 6px;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }
            .suggestions-chips {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .chip-btn {
                background: var(--surface, #ffffff);
                border: 1px solid var(--border, #dbe9e0);
                border-radius: 10px;
                padding: 7px 11px;
                font-size: 0.8rem;
                font-weight: 600;
                color: var(--primary, #0e7a4b);
                text-align: left;
                cursor: pointer;
                transition: background 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
                display: flex;
                align-items: center;
            }
            .chip-btn:hover {
                background: var(--primary-soft, #e3f4ea);
                border-color: var(--primary, #0e7a4b);
                transform: translateX(3px);
            }

            /* Chips Pertanyaan Terkait di Bawah Balasan Bot */
            .chat-chips-group {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin-top: 6px;
            }
            .chat-chip-btn {
                background: var(--surface, #ffffff);
                border: 1px solid var(--border, #cce4d4);
                border-radius: 9999px;
                padding: 5px 12px;
                font-size: 0.78rem;
                font-weight: 600;
                color: var(--primary, #0e7a4b);
                text-align: left;
                cursor: pointer;
                box-shadow: 0 1px 3px rgba(15, 42, 29, 0.05);
                transition: background 0.15s ease, border-color 0.15s ease, transform 0.15s ease, color 0.15s ease;
                display: inline-flex;
                align-items: center;
                line-height: 1.25;
            }
            .chat-chip-btn:hover {
                background: var(--primary, #0e7a4b);
                border-color: var(--primary, #0e7a4b);
                color: #ffffff;
                transform: translateY(-1px);
            }

            /* Footer Form */
            .ai-chat-footer {
                padding: 10px 14px;
                background: var(--surface, #ffffff);
                border-top: 1px solid var(--border, #dbe9e0);
                display: flex;
                align-items: center;
                gap: 8px;
                flex-shrink: 0;
            }
            .ai-chat-input {
                flex-grow: 1;
                border: 1px solid var(--border, #dbe9e0);
                border-radius: 12px;
                padding: 9px 12px;
                font-size: 0.88rem;
                background: var(--surface-2, #f7faf8);
                color: var(--ink, #0f2a1d);
                outline: none;
                transition: border-color 0.15s ease;
            }
            .ai-chat-input:focus {
                border-color: var(--primary, #0e7a4b);
                background: var(--surface, #ffffff);
            }
            .ai-chat-send {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                border: none;
                background: var(--primary, #0e7a4b);
                color: #ffffff;
                display: grid;
                place-items: center;
                cursor: pointer;
                font-size: 0.95rem;
                transition: background 0.15s ease, transform 0.15s ease;
                flex-shrink: 0;
            }
            .ai-chat-send:hover {
                background: var(--primary-dark, #0a5c38);
                transform: scale(1.04);
            }
            .ai-chat-send:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }

            /* Dark Mode Styling */
            [data-bs-theme="dark"] .whatsapp-popover-menu,
            [data-theme="dark"] .whatsapp-popover-menu {
                background: #12201a;
                border-color: #233f32;
                box-shadow: 0 16px 36px rgba(0, 0, 0, 0.45);
            }
            [data-bs-theme="dark"] .wa-popover-header,
            [data-theme="dark"] .wa-popover-header {
                color: #8fa699;
                border-bottom-color: #233f32;
            }
            [data-bs-theme="dark"] .wa-menu-item:hover,
            [data-theme="dark"] .wa-menu-item:hover {
                background: #1a2f26;
            }
            [data-bs-theme="dark"] .wa-menu-title,
            [data-theme="dark"] .wa-menu-title {
                color: #f7faf8;
            }
            [data-bs-theme="dark"] .wa-menu-desc,
            [data-theme="dark"] .wa-menu-desc {
                color: #9cb1a5;
            }
            [data-bs-theme="dark"] .ai-chat-window,
            [data-theme="dark"] .ai-chat-window {
                background: #12201a;
                border-color: #233f32;
                box-shadow: 0 20px 48px rgba(0, 0, 0, 0.55);
            }
            [data-bs-theme="dark"] .ai-chat-body,
            [data-theme="dark"] .ai-chat-body {
                background: #0b1511;
            }
            [data-bs-theme="dark"] .chat-msg-bot .chat-bubble,
            [data-theme="dark"] .chat-msg-bot .chat-bubble {
                background: #182c24;
                color: #e5f1ea;
                border-color: #233f32;
            }
            [data-bs-theme="dark"] .ai-chat-disclaimer,
            [data-theme="dark"] .ai-chat-disclaimer {
                background: #162921;
                border-bottom-color: #233f32;
                color: #a0b6ab;
            }
            [data-bs-theme="dark"] .chat-chip-btn,
            [data-theme="dark"] .chat-chip-btn {
                background: #182c24;
                border-color: #2b4b3c;
                color: #4ade80;
            }
            [data-bs-theme="dark"] .chat-chip-btn:hover,
            [data-theme="dark"] .chat-chip-btn:hover {
                background: #0e7a4b;
                border-color: #0e7a4b;
                color: #ffffff;
            }
            [data-bs-theme="dark"] .ai-chat-footer,
            [data-theme="dark"] .ai-chat-footer {
                background: #12201a;
                border-top-color: #233f32;
            }
            [data-bs-theme="dark"] .ai-chat-input,
            [data-theme="dark"] .ai-chat-input {
                background: #1a2f26;
                border-color: #2d4e3f;
                color: #f7faf8;
            }
            [data-bs-theme="dark"] .chip-btn,
            [data-theme="dark"] .chip-btn {
                background: #182c24;
                border-color: #284739;
                color: #4ade80;
            }
            [data-bs-theme="dark"] .chip-btn:hover,
            [data-theme="dark"] .chip-btn:hover {
                background: #203c30;
                border-color: #4ade80;
            }

            /* Label Melayang Tidak Boleh Menimpa Jendela Chat */
            .whatsapp-help-container.is-chat-active .whatsapp-help__label {
                display: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }

            /* Responsive HP (320px, 360px, 400px, 576px, 768px) */
            @media (max-width: 767.98px) {
                .whatsapp-help-container {
                    right: max(16px, env(safe-area-inset-right, 16px));
                    bottom: max(82px, calc(76px + env(safe-area-inset-bottom, 0px)));
                }
                .whatsapp-help__button {
                    width: 50px;
                    height: 50px;
                    flex-basis: 50px;
                    font-size: 25px;
                }
                .whatsapp-help__label {
                    display: none !important;
                }
                .whatsapp-popover-menu {
                    width: calc(100vw - 32px);
                    max-width: 290px;
                    right: 0;
                    bottom: calc(100% + 12px);
                }
                .ai-chat-window {
                    right: 12px;
                    bottom: 144px;
                    width: calc(100vw - 24px);
                    max-width: 440px;
                    max-height: calc(100dvh - 160px);
                    height: min(520px, calc(100dvh - 160px));
                    border-radius: 18px;
                }
            }

            /* Aksesibilitas Prefers Reduced Motion */
            @media (prefers-reduced-motion: reduce) {
                .whatsapp-help__label,
                .whatsapp-help__button,
                .whatsapp-help__pulse,
                .whatsapp-help__icon-wrap,
                .wa-icon-main,
                .wa-icon-close,
                .whatsapp-popover-menu,
                .ai-chat-window,
                .wa-menu-item,
                .wa-menu-arrow,
                .chip-btn,
                .ai-chat-send {
                    transition: none !important;
                    animation: none !important;
                }
                .whatsapp-help.is-open .wa-icon-main {
                    display: none !important;
                }
                .whatsapp-help:not(.is-open) .wa-icon-close {
                    display: none !important;
                }
            }

            @media print {
                .whatsapp-help-container {
                    display: none !important;
                }
            }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const waHelpContainer = document.getElementById('waHelpContainer');
            const btnWaToggle = document.getElementById('btnWaToggle');
            const waHelpMenu = document.getElementById('waHelpMenu');
            const menuItemLiveChat = document.getElementById('menuItemLiveChat');
            const menuItemWaCs = document.getElementById('menuItemWaCs');
            const aiChatWindow = document.getElementById('aiChatWindow');
            const btnCloseAiChat = document.getElementById('btnCloseAiChat');
            const aiChatForm = document.getElementById('aiChatForm');
            const aiChatInput = document.getElementById('aiChatInput');
            const aiChatBody = document.getElementById('aiChatBody');
            const btnSendAiChat = document.getElementById('btnSendAiChat');
            const suggestionChips = document.querySelectorAll('.chip-btn');

            let isMenuOpen = false;
            let isChatOpen = false;

            // Bersihkan riwayat chat jika berganti akun / logout / login baru (Poin 1)
            const currentUserId = waHelpContainer?.dataset.currentUserId || 'guest';
            const previousUserId = sessionStorage.getItem('smartrw_chat_user_id');
            if (previousUserId && previousUserId !== currentUserId) {
                sessionStorage.removeItem('smartrw_chat_history');
            }
            sessionStorage.setItem('smartrw_chat_user_id', currentUserId);

            // Buka / Tutup Popover Menu
            function toggleMenu(open) {
                isMenuOpen = typeof open === 'boolean' ? open : !isMenuOpen;
                if (isMenuOpen) {
                    waHelpMenu.classList.add('active');
                    waHelpMenu.setAttribute('aria-hidden', 'false');
                    btnWaToggle.classList.add('is-open');
                    btnWaToggle.setAttribute('aria-expanded', 'true');
                    btnWaToggle.setAttribute('aria-label', 'Tutup menu bantuan');
                    btnWaToggle.setAttribute('title', 'Tutup menu bantuan');
                    // Focus ke item pertama
                    menuItemWaCs?.setAttribute('tabindex', '0');
                    menuItemLiveChat?.setAttribute('tabindex', '0');
                } else {
                    waHelpMenu.classList.remove('active');
                    waHelpMenu.setAttribute('aria-hidden', 'true');
                    btnWaToggle.classList.remove('is-open');
                    btnWaToggle.setAttribute('aria-expanded', 'false');
                    btnWaToggle.setAttribute('aria-label', 'Buka menu bantuan');
                    btnWaToggle.setAttribute('title', 'Buka menu bantuan');
                    menuItemWaCs?.setAttribute('tabindex', '-1');
                    menuItemLiveChat?.setAttribute('tabindex', '-1');
                }
            }

            // Buka / Tutup Jendela Chat
            function toggleChat(open) {
                isChatOpen = typeof open === 'boolean' ? open : !isChatOpen;
                if (isChatOpen) {
                    waHelpContainer?.classList.add('is-chat-active');
                    toggleMenu(false);
                    aiChatWindow.style.display = 'flex';
                    aiChatWindow.setAttribute('aria-modal', 'true');
                    setTimeout(() => {
                        aiChatInput?.focus();
                        scrollToBottom();
                    }, 50);
                } else {
                    waHelpContainer?.classList.remove('is-chat-active');
                    aiChatWindow.style.display = 'none';
                    aiChatWindow.setAttribute('aria-modal', 'false');
                    btnWaToggle?.focus();
                }
            }

            // Event Listeners Tombol Utama
            btnWaToggle?.addEventListener('click', function (e) {
                e.stopPropagation();
                if (isChatOpen) {
                    toggleChat(false);
                } else {
                    toggleMenu();
                }
            });

            // Klik Menu Live Chat AI
            menuItemLiveChat?.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleChat(true);
            });

            // Tombol Tutup Chat
            btnCloseAiChat?.addEventListener('click', function () {
                toggleChat(false);
            });

            // Klik di luar menutup Popover
            document.addEventListener('click', function (e) {
                if (isMenuOpen && !waHelpMenu.contains(e.target) && !btnWaToggle.contains(e.target)) {
                    toggleMenu(false);
                }
            });

            // Keyboard Accessibility: Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (isChatOpen) {
                        toggleChat(false);
                    } else if (isMenuOpen) {
                        toggleMenu(false);
                        btnWaToggle.focus();
                    }
                }
            });

            // Keyboard navigation antar menu item (Arrow Up/Down)
            waHelpMenu?.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (document.activeElement === menuItemWaCs) {
                        menuItemLiveChat.focus();
                    } else {
                        menuItemWaCs.focus();
                    }
                }
            });

            function scrollToBottom() {
                if (aiChatBody) {
                    aiChatBody.scrollTop = aiChatBody.scrollHeight;
                }
            }

            function appendMessage(sender, text, chips = [], requiresLogin = false) {
                const now = new Date();
                const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

                const msgDiv = document.createElement('div');
                msgDiv.className = `chat-msg chat-msg-${sender}`;

                const bubbleDiv = document.createElement('div');
                bubbleDiv.className = 'chat-bubble';

                const textSpan = document.createElement('span');
                textSpan.textContent = text;
                bubbleDiv.appendChild(textSpan);

                // Jika respons memerlukan login (untuk tamu)
                if (sender === 'bot' && requiresLogin) {
                    const loginWrap = document.createElement('div');
                    loginWrap.className = 'mt-2 pt-1 border-top border-light-subtle';
                    const loginUrl = waHelpContainer?.dataset.loginUrl || '/login';
                    loginWrap.innerHTML = `
                        <a href="${loginUrl}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold text-white shadow-sm" style="font-size: 0.78rem;">
                            <i class="fas fa-right-to-bracket me-1"></i> Masuk
                        </a>
                    `;
                    bubbleDiv.appendChild(loginWrap);
                }

                const timeDiv = document.createElement('div');
                timeDiv.className = 'chat-time';
                timeDiv.textContent = timeStr;

                msgDiv.appendChild(bubbleDiv);

                // Tambahkan chip interaktif jika ada saran / pilihan maksud
                if (sender === 'bot' && Array.isArray(chips) && chips.length > 0) {
                    const chipsGroup = document.createElement('div');
                    chipsGroup.className = 'chat-chips-group';
                    chips.forEach(chipText => {
                        if (typeof chipText === 'string' && chipText.trim()) {
                            const chipBtn = document.createElement('button');
                            chipBtn.type = 'button';
                            chipBtn.className = 'chat-chip-btn';
                            chipBtn.textContent = chipText.trim();
                            chipBtn.addEventListener('click', function () {
                                sendMessage(chipText.trim());
                            });
                            chipsGroup.appendChild(chipBtn);
                        }
                    });
                    msgDiv.appendChild(chipsGroup);
                }

                msgDiv.appendChild(timeDiv);
                aiChatBody.appendChild(msgDiv);

                scrollToBottom();
            }

            function appendLoading() {
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'chat-msg chat-msg-bot';
                loadingDiv.id = 'chatLoadingIndicator';
                loadingDiv.innerHTML = `
                    <div class="chat-bubble" style="opacity: 0.75; font-style: italic;">
                        <i class="fas fa-circle-notch fa-spin me-1.5 text-primary"></i> Sedang mencari jawaban...
                    </div>
                `;
                aiChatBody.appendChild(loadingDiv);
                scrollToBottom();
                return loadingDiv;
            }

            function removeLoading() {
                const el = document.getElementById('chatLoadingIndicator');
                if (el) el.remove();
            }

            // Kirim pesan ke Backend
            async function sendMessage(text) {
                const trimmed = text.trim();
                if (!trimmed) return;

                appendMessage('user', trimmed);
                if (aiChatInput) aiChatInput.value = '';

                btnSendAiChat.disabled = true;
                appendLoading();

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                try {
                    const response = await fetch('/api/assistant/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ message: trimmed })
                    });

                    removeLoading();

                    if (response.status === 429) {
                        appendMessage('bot', 'Batas pertanyaan tercapai (maksimal 20 pesan per jam). Silakan hubungi pengurus via WhatsApp jika memerlukan bantuan segera.');
                        return;
                    }

                    if (!response.ok) {
                        throw new Error('Respons jaringan bermasalah.');
                    }

                    const data = await response.json();
                    const chips = data.choices || data.related_chips || data.suggestion_chips || data.quick_replies || [];
                    appendMessage('bot', data.reply || 'Maaf, terjadi kesalahan. Silakan coba kembali.', chips, data.requires_login || false);
                } catch (err) {
                    removeLoading();
                    appendMessage('bot', 'Maaf, sistem asisten sedang tidak dapat dihubungi. Silakan hubungi pengurus via WhatsApp.');
                } finally {
                    btnSendAiChat.disabled = false;
                    aiChatInput?.focus();
                }
            }

            // Event Submit Chat
            aiChatForm?.addEventListener('submit', function (e) {
                e.preventDefault();
                const text = aiChatInput.value;
                sendMessage(text);
            });

            // Klik Saran Pertanyaan Cepat (Chips)
            suggestionChips.forEach(chip => {
                chip.addEventListener('click', function () {
                    const query = this.dataset.query || this.textContent.trim();
                    sendMessage(query);
                });
            });
        });
        </script>
    @endonce
@endif
