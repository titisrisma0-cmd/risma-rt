<style>
    .layanan-page {
        color: #fffbf0;
    }

    .page-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-head h2 {
        color: #fffbf0;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .page-head p {
        color: #cbd5e1;
        margin-bottom: 0;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .product-card {
        display: flex;
        min-height: 100%;
        flex-direction: column;
        gap: 8px;
        padding: 14px;
        border: 1px solid rgba(255, 215, 0, 0.16);
        border-radius: 16px;
        background: rgba(255, 215, 0, 0.05);
    }

    .product-photo {
        aspect-ratio: 16 / 10;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 14px;
        background: rgba(15, 23, 42, 0.45);
        color: #ffd700;
        font-size: 2rem;
    }

    .product-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-card strong {
        color: #fffbf0;
        font-size: 1rem;
    }

    .product-card span,
    .product-card p {
        color: #cbd5e1;
        font-size: 0.9rem;
        line-height: 1.45;
        margin-bottom: 0;
    }

    .product-card b {
        color: #ffd700;
    }

    .empty-state {
        grid-column: 1 / -1;
        padding: 28px;
        border: 1px dashed rgba(255, 215, 0, 0.25);
        border-radius: 16px;
        color: #cbd5e1;
        text-align: center;
        background: rgba(255, 215, 0, 0.04);
    }

    .timeline-item,
    .child-card,
    .contact-item,
    .tip-box {
        display: block;
        padding: 14px;
        border: 1px solid rgba(255, 215, 0, 0.16);
        border-radius: 14px;
        color: inherit;
        text-decoration: none;
        background: rgba(255, 215, 0, 0.05);
        margin-bottom: 12px;
    }

    .timeline-item strong,
    .contact-item strong {
        display: block;
        color: #fffbf0;
        margin-bottom: 4px;
    }

    .timeline-item span,
    .contact-item span {
        color: #cbd5e1;
        font-size: 0.9rem;
    }

    .panic-btn {
        width: 100%;
        border: none;
        border-radius: 16px;
        padding: 14px 16px;
        background: linear-gradient(135deg, #ef4444, #f97316);
        color: white;
        font-weight: 800;
    }

    .surat-guide {
        min-height: 132px;
        padding: 16px;
        border: 1px solid rgba(255, 215, 0, 0.16);
        border-radius: 16px;
        background: rgba(255, 215, 0, 0.05);
    }

    .surat-guide i {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        margin-bottom: 12px;
        color: #ffd700;
        background: rgba(255, 215, 0, 0.12);
    }

    .surat-guide strong,
    .surat-history-item strong {
        display: block;
        color: #fffbf0;
        margin-bottom: 5px;
    }

    .surat-guide span,
    .surat-history-item span,
    .surat-history-item p {
        color: #cbd5e1;
        font-size: 0.92rem;
        line-height: 1.5;
        margin-bottom: 0;
    }

    .surat-history-item {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 16px;
        border: 1px solid rgba(255, 215, 0, 0.14);
        border-radius: 16px;
        background: rgba(255, 215, 0, 0.045);
        margin-bottom: 12px;
    }

    .surat-note {
        display: inline-block;
        padding: 7px 10px;
        border-radius: 10px;
        color: #fde68a;
        background: rgba(255, 215, 0, 0.08);
    }

    .surat-file-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .surat-file-list a {
        display: inline-flex;
        align-items: center;
        padding: 5px 8px;
        border-radius: 999px;
        color: #a5f3fc;
        text-decoration: none;
        background: rgba(14, 165, 233, 0.12);
        border: 1px solid rgba(14, 165, 233, 0.2);
        font-size: 0.82rem;
    }

    .surat-action-form {
        display: grid;
        gap: 8px;
    }

    .posyandu-guide {
        min-height: 132px;
        padding: 16px;
        border: 1px solid rgba(255, 215, 0, 0.16);
        border-radius: 16px;
        background: rgba(255, 215, 0, 0.05);
    }

    .posyandu-guide i {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        margin-bottom: 12px;
        color: #ffd700;
        background: rgba(255, 215, 0, 0.12);
    }

    .posyandu-guide strong,
    .child-card-head h6 {
        display: block;
        color: #fffbf0;
        margin-bottom: 5px;
    }

    .posyandu-guide span,
    .child-card-head span {
        color: #cbd5e1;
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .posyandu-schedule {
        display: grid;
        grid-template-columns: 62px 1fr;
        gap: 14px;
        align-items: start;
    }

    .schedule-date {
        width: 58px;
        min-height: 58px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: rgba(255, 215, 0, 0.12);
        border: 1px solid rgba(255, 215, 0, 0.2);
    }

    .schedule-date strong {
        color: #ffd700;
        font-size: 1.2rem;
        margin-bottom: 0;
    }

    .schedule-date span {
        color: #cbd5e1;
        font-size: 0.78rem;
        text-transform: uppercase;
    }

    .child-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .growth-chart-wrap {
        min-height: 180px;
        padding: 12px;
        border: 1px solid rgba(255, 215, 0, 0.12);
        border-radius: 14px;
        background: rgba(15, 23, 42, 0.24);
    }

    .posyandu-checklist {
        display: grid;
        gap: 10px;
        padding-left: 18px;
        margin-bottom: 0;
        color: #cbd5e1;
    }

    .posyandu-dashboard {
        color: #1f2933;
    }

    .posyandu-board {
        display: grid;
        grid-template-columns: 1.05fr 1fr 0.95fr;
        gap: 14px;
    }

    .pos-board-card {
        overflow: hidden;
        border: 1px solid rgba(125, 138, 113, 0.2);
        border-radius: 12px;
        background: rgba(248, 250, 244, 0.94);
        box-shadow: 0 12px 28px rgba(10, 24, 20, 0.16);
        color: #1f2933;
    }

    .pos-wide {
        grid-column: span 2;
    }

    .pos-card-title {
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 12px;
        background: linear-gradient(180deg, #d9f1fb, #c6e6f3);
        border-bottom: 1px solid rgba(93, 124, 133, 0.2);
        color: #243126;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.86rem;
    }

    .pos-card-title i {
        color: #2486c7;
    }

    .next-schedule-body {
        display: grid;
        grid-template-columns: 64px 1fr 70px;
        gap: 12px;
        align-items: center;
        padding: 14px 12px 10px;
    }

    .calendar-icon {
        position: relative;
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #e7f6fb;
        color: #f59e0b;
        font-size: 2rem;
    }

    .calendar-icon small {
        position: absolute;
        right: -4px;
        bottom: -4px;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #3b82f6;
        color: white;
        font-size: 0.74rem;
    }

    .next-schedule-main h3 {
        margin: 0 0 4px;
        color: #111827;
        font-size: clamp(1.1rem, 2vw, 1.45rem);
        font-weight: 900;
        line-height: 1.15;
    }

    .next-schedule-main span {
        color: #4b5563;
        font-weight: 700;
    }

    .days-left {
        text-align: center;
        color: #111827;
    }

    .days-left strong {
        display: block;
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
    }

    .days-left span {
        display: block;
        color: #4b5563;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .registered-count {
        padding: 10px 14px 4px;
        color: #111827;
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
    }

    .registered-count span {
        font-size: 1rem;
        font-weight: 800;
    }

    .registered-list {
        display: grid;
        gap: 6px;
        padding: 4px 14px 14px;
    }

    .registered-list div {
        display: grid;
        grid-template-columns: 26px 1fr auto;
        gap: 8px;
        align-items: center;
    }

    .avatar-dot {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 900;
    }

    .registered-list strong {
        color: #1f2933;
        font-size: 0.9rem;
    }

    .registered-list small {
        color: #64748b;
        font-size: 0.78rem;
        text-align: right;
    }

    .nutrition-summary {
        display: grid;
        grid-template-columns: 68px 1fr 82px;
        gap: 12px;
        align-items: center;
        padding: 14px;
    }

    .child-photo {
        width: 62px;
        height: 62px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: linear-gradient(135deg, #fde68a, #f9a8d4);
        color: #7c2d12;
        font-size: 2rem;
    }

    .nutrition-main strong {
        display: block;
        color: #111827;
        font-size: 1.08rem;
    }

    .nutrition-main span {
        display: block;
        color: #4b5563;
        line-height: 1.35;
        font-weight: 700;
    }

    .nutrition-donut {
        width: 74px;
        height: 74px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background:
            radial-gradient(circle at center, #fff 0 48%, transparent 49%),
            conic-gradient(#35b779 calc(var(--value) * 1%), #f59e0b 0 88%, #ef4444 0);
    }

    .nutrition-donut strong {
        color: #0f766e;
        font-size: 0.98rem;
        font-weight: 900;
    }

    .nutrition-legend,
    .immun-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        padding: 0 14px 12px;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .nutrition-legend i,
    .immun-legend i {
        width: 9px;
        height: 9px;
        display: inline-block;
        border-radius: 50%;
        margin-right: 4px;
    }

    .legend-ok,
    .green {
        background: #35b779;
    }

    .legend-watch,
    .amber {
        background: #f59e0b;
    }

    .red {
        background: #ef4444;
    }

    .pos-filter {
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: none;
        font-weight: 700;
    }

    .pos-filter input {
        width: 170px;
        height: 28px;
        border: 1px solid rgba(100, 116, 139, 0.28);
        border-radius: 8px;
        padding: 4px 8px;
        color: #1f2933;
        background: white;
        font-size: 0.8rem;
    }

    .pos-table {
        margin-bottom: 0;
        color: #1f2933;
        --bs-table-bg: transparent;
        --bs-table-color: #1f2933;
        --bs-table-border-color: rgba(148, 163, 184, 0.34);
        --bs-table-hover-bg: rgba(219, 234, 254, 0.55);
    }

    .pos-table thead th {
        background: #d9edf7;
        color: #334155;
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .pos-table td {
        font-size: 0.88rem;
        vertical-align: middle;
    }

    .status-pill,
    .metric-ok,
    .metric-warn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-pill {
        padding: 4px 8px;
        border-radius: 999px;
        color: #166534;
        background: #dcfce7;
    }

    .metric-ok {
        color: #16a34a;
    }

    .metric-warn {
        color: #ca8a04;
    }

    .tips-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        padding: 12px;
    }

    .tip-tile {
        min-height: 92px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px;
        border-radius: 12px;
        color: #1f2933;
        background: #d9f5e1;
    }

    .tip-tile:nth-child(1) {
        background: #c7eff9;
    }

    .tip-tile:nth-child(3) {
        background: #dff4ce;
    }

    .tip-tile strong {
        display: block;
        line-height: 1.15;
        margin-bottom: 5px;
        font-weight: 900;
    }

    .tip-tile span {
        display: block;
        color: #475569;
        font-size: 0.75rem;
        line-height: 1.3;
    }

    .tip-tile i {
        color: #15803d;
        font-size: 2rem;
    }

    .quick-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        padding: 12px;
    }

    .quick-grid a {
        min-height: 92px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 12px;
        color: #1f2933;
        text-align: center;
        text-decoration: none;
        background: white;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
        font-weight: 800;
        line-height: 1.15;
        transition: transform 0.2s ease;
    }

    .quick-grid a:hover {
        transform: translateY(-3px);
    }

    .quick-grid i {
        color: #2486c7;
        font-size: 1.55rem;
    }

    .schedule-feature-grid,
    .gallery-feature-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .schedule-feature-card,
    .gallery-feature-card {
        min-height: 128px;
        display: flex;
        gap: 12px;
        padding: 14px;
        border: 1px solid rgba(255, 215, 0, 0.16);
        border-radius: 14px;
        background: rgba(255, 215, 0, 0.05);
    }

    .schedule-feature-card strong,
    .gallery-feature-card strong {
        display: block;
        color: #fffbf0;
        margin-bottom: 5px;
    }

    .schedule-feature-card span,
    .schedule-feature-card p,
    .gallery-feature-card span {
        display: block;
        color: #cbd5e1;
        font-size: 0.86rem;
        line-height: 1.42;
        margin-bottom: 0;
    }

    .gallery-feature-card {
        flex-direction: column;
    }

    .gallery-feature-card i {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        color: #2486c7;
        background: rgba(191, 219, 254, 0.92);
        font-size: 1.25rem;
    }

    .queue-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }

    .queue-summary div {
        padding: 12px;
        border-radius: 12px;
        background: rgba(255, 215, 0, 0.07);
        border: 1px solid rgba(255, 215, 0, 0.14);
        text-align: center;
    }

    .queue-summary strong {
        display: block;
        color: #ffd700;
        font-size: 1.45rem;
        line-height: 1;
    }

    .queue-summary span {
        color: #cbd5e1;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .queue-action {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border-radius: 12px;
        color: #fffbf0;
        text-decoration: none;
        background: rgba(14, 165, 233, 0.14);
        border: 1px solid rgba(14, 165, 233, 0.22);
        margin-bottom: 14px;
    }

    .queue-action:hover {
        color: #fffbf0;
    }

    .queue-action i {
        color: #7dd3fc;
        font-size: 1.2rem;
    }

    .queue-steps {
        display: grid;
        gap: 7px;
        margin-bottom: 0;
        padding-left: 20px;
        color: #cbd5e1;
    }

    .ronda-alert {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 14px 16px;
        border: 1px solid rgba(248, 113, 113, 0.35);
        border-radius: 16px;
        background: rgba(239, 68, 68, 0.18);
        color: #fee2e2;
        box-shadow: 0 12px 28px rgba(127, 29, 29, 0.18);
    }

    .ronda-alert i {
        color: #fca5a5;
        font-size: 1.6rem;
    }

    .ronda-alert strong,
    .panic-panel h5 {
        display: block;
        color: #fff;
        margin-bottom: 3px;
        font-weight: 900;
    }

    .ronda-alert span,
    .panic-panel p {
        color: #fecaca;
        margin-bottom: 0;
        line-height: 1.45;
    }

    .ronda-summary-card {
        min-height: 110px;
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 18px;
        border: 1px solid rgba(255, 215, 0, 0.16);
        border-radius: 16px;
        background: rgba(255, 215, 0, 0.05);
    }

    .ronda-summary-card i {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        color: #fca5a5;
        background: rgba(239, 68, 68, 0.16);
        font-size: 1.35rem;
    }

    .ronda-summary-card strong {
        display: block;
        color: #fffbf0;
        font-size: 1.8rem;
        line-height: 1;
    }

    .ronda-summary-card span {
        color: #cbd5e1;
        font-weight: 700;
    }

    .ronda-schedule-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .ronda-schedule-card {
        display: grid;
        grid-template-columns: 58px 1fr;
        gap: 12px;
        padding: 14px;
        border: 1px solid rgba(255, 215, 0, 0.16);
        border-radius: 16px;
        background: rgba(255, 215, 0, 0.05);
    }

    .ronda-schedule-card.is-mine {
        border-color: rgba(34, 197, 94, 0.28);
        background: rgba(34, 197, 94, 0.08);
    }

    .ronda-date {
        width: 54px;
        min-height: 54px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        color: #fee2e2;
        background: rgba(239, 68, 68, 0.18);
    }

    .ronda-date strong {
        color: #fff;
        font-size: 1.2rem;
        line-height: 1;
    }

    .ronda-date span,
    .ronda-schedule-card span,
    .ronda-schedule-card small {
        color: #cbd5e1;
    }

    .ronda-schedule-card > div:last-child strong {
        display: block;
        color: #fffbf0;
        margin-bottom: 4px;
    }

    .ronda-schedule-card small {
        display: block;
        margin-top: 6px;
        line-height: 1.35;
    }

    .ronda-mini-stat {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        margin: 2px;
    }

    .ronda-mini-stat.hadir {
        color: #bbf7d0;
        background: rgba(34, 197, 94, 0.18);
    }

    .ronda-mini-stat.izin {
        color: #bfdbfe;
        background: rgba(59, 130, 246, 0.18);
    }

    .ronda-mini-stat.alpa {
        color: #fecaca;
        background: rgba(239, 68, 68, 0.18);
    }

    .emergency-contact {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .emergency-contact > i {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        color: #fecaca;
        background: rgba(239, 68, 68, 0.18);
    }

    .panic-panel {
        border-color: rgba(239, 68, 68, 0.32) !important;
        background: linear-gradient(135deg, rgba(127, 29, 29, 0.48), rgba(30, 41, 59, 0.58)) !important;
    }

    .panic-btn i {
        margin-right: 6px;
    }

    html[data-theme="light"] .layanan-page,
    html[data-theme="light"] .page-head h2,
    html[data-theme="light"] .product-card strong,
    html[data-theme="light"] .timeline-item strong,
    html[data-theme="light"] .contact-item strong,
    html[data-theme="light"] .surat-guide strong,
    html[data-theme="light"] .surat-history-item strong,
    html[data-theme="light"] .posyandu-guide strong,
    html[data-theme="light"] .child-card-head h6 {
        color: #1f2933;
    }

    html[data-theme="light"] .page-head p,
    html[data-theme="light"] .product-card span,
    html[data-theme="light"] .product-card p,
    html[data-theme="light"] .timeline-item span,
    html[data-theme="light"] .contact-item span,
    html[data-theme="light"] .empty-state,
    html[data-theme="light"] .surat-guide span,
    html[data-theme="light"] .surat-history-item span,
    html[data-theme="light"] .surat-history-item p,
    html[data-theme="light"] .posyandu-guide span,
    html[data-theme="light"] .child-card-head span,
    html[data-theme="light"] .posyandu-checklist,
    html[data-theme="light"] .schedule-feature-card span,
    html[data-theme="light"] .schedule-feature-card p,
    html[data-theme="light"] .gallery-feature-card span,
    html[data-theme="light"] .queue-summary span,
    html[data-theme="light"] .queue-steps {
        color: #687466;
    }

    html[data-theme="light"] .product-card,
    html[data-theme="light"] .timeline-item,
    html[data-theme="light"] .child-card,
    html[data-theme="light"] .contact-item,
    html[data-theme="light"] .tip-box,
    html[data-theme="light"] .empty-state,
    html[data-theme="light"] .surat-guide,
    html[data-theme="light"] .surat-history-item,
    html[data-theme="light"] .posyandu-guide,
    html[data-theme="light"] .schedule-feature-card,
    html[data-theme="light"] .gallery-feature-card,
    html[data-theme="light"] .queue-summary div {
        background: rgba(226, 233, 218, 0.45);
        border-color: rgba(125, 138, 113, 0.24);
    }

    html[data-theme="light"] .schedule-feature-card strong,
    html[data-theme="light"] .gallery-feature-card strong {
        color: #1f2933;
    }

    html[data-theme="light"] .queue-summary strong {
        color: #8a7428;
    }

    html[data-theme="light"] .queue-action {
        color: #1f2933;
        background: rgba(219, 234, 254, 0.65);
        border-color: rgba(96, 165, 250, 0.28);
    }

    html[data-theme="light"] .queue-action:hover {
        color: #1f2933;
    }

    html[data-theme="light"] .ronda-alert {
        color: #7f1d1d;
        background: rgba(254, 226, 226, 0.78);
        border-color: rgba(248, 113, 113, 0.32);
    }

    html[data-theme="light"] .ronda-alert strong,
    html[data-theme="light"] .panic-panel h5,
    html[data-theme="light"] .ronda-summary-card strong,
    html[data-theme="light"] .ronda-schedule-card > div:last-child strong {
        color: #1f2933;
    }

    html[data-theme="light"] .ronda-alert span,
    html[data-theme="light"] .panic-panel p,
    html[data-theme="light"] .ronda-summary-card span,
    html[data-theme="light"] .ronda-date span,
    html[data-theme="light"] .ronda-schedule-card span,
    html[data-theme="light"] .ronda-schedule-card small {
        color: #687466;
    }

    html[data-theme="light"] .ronda-summary-card,
    html[data-theme="light"] .ronda-schedule-card {
        background: rgba(226, 233, 218, 0.45);
        border-color: rgba(125, 138, 113, 0.24);
    }

    html[data-theme="light"] .ronda-date {
        background: rgba(254, 226, 226, 0.82);
    }

    html[data-theme="light"] .ronda-date strong {
        color: #b91c1c;
    }

    html[data-theme="light"] .ronda-mini-stat.hadir {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #86efac;
    }

    html[data-theme="light"] .ronda-mini-stat.izin {
        color: #1e40af;
        background: #dbeafe;
        border: 1px solid #93c5fd;
    }

    html[data-theme="light"] .ronda-mini-stat.alpa {
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fca5a5;
    }

    html[data-theme="light"] .panic-panel {
        background: rgba(248, 250, 244, 0.86) !important;
    }

    html[data-theme="light"] .surat-guide i,
    html[data-theme="light"] .posyandu-guide i {
        color: #8a7428;
        background: rgba(138, 116, 40, 0.12);
    }

    html[data-theme="light"] .schedule-date,
    html[data-theme="light"] .growth-chart-wrap {
        background: rgba(226, 233, 218, 0.36);
        border-color: rgba(125, 138, 113, 0.2);
    }

    html[data-theme="light"] .schedule-date strong {
        color: #8a7428;
    }

    html[data-theme="light"] .schedule-date span {
        color: #687466;
    }

    html[data-theme="light"] .surat-note {
        color: #6b5a17;
        background: rgba(138, 116, 40, 0.1);
    }

    @media (max-width: 768px) {
        .page-head {
            flex-direction: column;
        }

        .product-grid {
            grid-template-columns: 1fr;
        }

        .posyandu-board,
        .tips-grid,
        .quick-grid,
        .schedule-feature-grid,
        .gallery-feature-grid {
            grid-template-columns: 1fr;
        }

        .queue-summary {
            grid-template-columns: 1fr;
        }

        .ronda-schedule-grid {
            grid-template-columns: 1fr;
        }

        .pos-wide {
            grid-column: span 1;
        }

        .next-schedule-body,
        .nutrition-summary {
            grid-template-columns: 1fr;
        }

        .days-left {
            text-align: left;
        }

        .pos-filter {
            width: 100%;
            align-items: stretch;
            flex-direction: column;
        }

        .pos-filter input {
            width: 100%;
        }

        .surat-history-item {
            flex-direction: column;
        }

        .child-card-head {
            flex-direction: column;
        }
    }
</style>
