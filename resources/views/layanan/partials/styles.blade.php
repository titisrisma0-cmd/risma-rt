<style>
    .layanan-page {
        color: var(--text-main, #17221C);
    }

    .page-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-head h2 {
        color: var(--text-main, #17221C);
        font-weight: 800;
        margin-bottom: 6px;
    }

    .page-head p {
        color: var(--text-secondary, #758078);
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
        padding: 16px;
        border: 1px solid var(--border-color, #E9EEEA);
        border-radius: 16px;
        background: var(--bg-card, #FFFFFF);
        box-shadow: var(--shadow-card, 0 1px 3px rgba(23, 34, 28, 0.04));
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-card-hover, 0 6px 20px rgba(20, 122, 69, 0.08));
    }

    .product-photo {
        aspect-ratio: 16 / 10;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 12px;
        background: var(--soft-green, #E8F5EC);
        color: var(--primary-green, #147A45);
        font-size: 2rem;
    }

    .product-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-card strong {
        color: var(--text-main, #17221C);
        font-size: 1rem;
        font-weight: 700;
    }

    .product-card span,
    .product-card p {
        color: var(--text-secondary, #758078);
        font-size: 0.88rem;
        line-height: 1.45;
        margin-bottom: 0;
    }

    .product-card b {
        color: var(--primary-green, #147A45);
    }

    .empty-state {
        grid-column: 1 / -1;
        padding: 32px 20px;
        border: 2px dashed var(--border-color, #E9EEEA);
        border-radius: 16px;
        color: var(--text-secondary, #758078);
        text-align: center;
        background: var(--bg-card, #FFFFFF);
    }

    .timeline-item,
    .child-card,
    .contact-item,
    .tip-box {
        display: block;
        padding: 16px;
        border: 1px solid var(--border-color, #E9EEEA);
        border-radius: 14px;
        color: inherit;
        text-decoration: none;
        background: var(--bg-card, #FFFFFF);
        margin-bottom: 12px;
        box-shadow: var(--shadow-xs, 0 1px 2px rgba(23, 34, 28, 0.03));
    }

    .timeline-item strong,
    .contact-item strong {
        display: block;
        color: var(--text-main, #17221C);
        margin-bottom: 4px;
        font-weight: 700;
    }

    .timeline-item span,
    .contact-item span {
        color: var(--text-secondary, #758078);
        font-size: 0.875rem;
    }

    .panic-btn {
        width: 100%;
        border: none;
        border-radius: 14px;
        padding: 14px 16px;
        background: #E65353;
        color: white;
        font-weight: 800;
        box-shadow: 0 4px 14px rgba(230, 83, 83, 0.35);
        transition: all 0.2s ease;
    }

    .panic-btn:hover {
        background: #C93B3B;
        transform: translateY(-1px);
    }

    .surat-guide {
        min-height: 132px;
        padding: 18px;
        border: 1px solid var(--border-color, #E9EEEA);
        border-radius: 16px;
        background: var(--bg-card, #FFFFFF);
        box-shadow: var(--shadow-card);
    }

    .surat-guide i {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        margin-bottom: 12px;
        color: var(--primary-green, #147A45);
        background: var(--soft-green, #E8F5EC);
    }

    .surat-guide strong,
    .surat-history-item strong {
        display: block;
        color: var(--text-main, #17221C);
        margin-bottom: 5px;
        font-weight: 700;
    }

    .surat-guide span,
    .surat-history-item span,
    .surat-history-item p {
        color: var(--text-secondary, #758078);
        font-size: 0.875rem;
        line-height: 1.5;
        margin-bottom: 0;
    }

    .surat-history-item {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 16px;
        border: 1px solid var(--border-color, #E9EEEA);
        border-radius: 16px;
        background: var(--bg-card, #FFFFFF);
        margin-bottom: 12px;
        box-shadow: var(--shadow-xs);
    }

    .surat-note {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 8px;
        color: #9A6900;
        background: var(--color-warning-soft, #FEF8EC);
        border: 1px solid rgba(242, 184, 75, 0.3);
        font-size: 0.825rem;
    }

    .surat-file-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .surat-file-list a {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        color: var(--color-info, #3985E8);
        text-decoration: none;
        background: var(--color-info-soft, #EDF4FD);
        border: 1px solid rgba(57, 133, 232, 0.2);
        font-size: 0.82rem;
        font-weight: 600;
    }

    .posyandu-guide {
        min-height: 132px;
        padding: 18px;
        border: 1px solid var(--border-color, #E9EEEA);
        border-radius: 16px;
        background: var(--bg-card, #FFFFFF);
        box-shadow: var(--shadow-card);
    }

    .posyandu-guide i {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        margin-bottom: 12px;
        color: var(--primary-green, #147A45);
        background: var(--soft-green, #E8F5EC);
    }

    .posyandu-guide strong,
    .child-card-head h6 {
        display: block;
        color: var(--text-main, #17221C);
        margin-bottom: 5px;
        font-weight: 700;
    }

    .posyandu-guide span,
    .child-card-head span {
        color: var(--text-secondary, #758078);
        font-size: 0.875rem;
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
        border-radius: 14px;
        background: var(--soft-green, #E8F5EC);
        border: 1px solid rgba(20, 122, 69, 0.2);
    }

    .schedule-date strong {
        color: var(--primary-green, #147A45);
        font-size: 1.25rem;
        line-height: 1;
        margin-bottom: 2px;
    }

    .schedule-date span {
        color: var(--text-secondary, #758078);
        font-size: 0.72rem;
        text-transform: uppercase;
        font-weight: 700;
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
        padding: 14px;
        border: 1px solid var(--border-color, #E9EEEA);
        border-radius: 14px;
        background: var(--bg-app, #F6F8F7);
    }

    .posyandu-board {
        display: grid;
        grid-template-columns: 1.05fr 1fr 0.95fr;
        gap: 16px;
    }

    .pos-board-card {
        overflow: hidden;
        border: 1px solid var(--border-color, #E9EEEA);
        border-radius: 16px;
        background: var(--bg-card, #FFFFFF);
        box-shadow: var(--shadow-card);
        color: var(--text-main, #17221C);
    }

    .pos-wide {
        grid-column: span 2;
    }

    .pos-card-title {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 16px;
        background: var(--bg-card-subtle, #FAFCFA);
        border-bottom: 1px solid var(--border-color, #E9EEEA);
        color: var(--text-main, #17221C);
        font-weight: 700;
        font-size: 0.88rem;
    }

    .pos-card-title i {
        color: var(--primary-green, #147A45);
    }

    .next-schedule-body {
        display: grid;
        grid-template-columns: 64px 1fr 70px;
        gap: 14px;
        align-items: center;
        padding: 16px;
    }

    .calendar-icon {
        position: relative;
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: var(--soft-green, #E8F5EC);
        color: var(--primary-green, #147A45);
        font-size: 1.85rem;
    }

    .next-schedule-main h3 {
        margin: 0 0 4px;
        color: var(--text-main, #17221C);
        font-size: 1.25rem;
        font-weight: 800;
    }

    .next-schedule-main span {
        color: var(--text-secondary, #758078);
        font-size: 0.875rem;
    }

    .days-left strong {
        display: block;
        font-size: 1.85rem;
        line-height: 1;
        font-weight: 800;
        color: var(--primary-green, #147A45);
    }

    .days-left span {
        display: block;
        color: var(--text-secondary, #758078);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .registered-count {
        padding: 14px 16px 6px;
        color: var(--text-main, #17221C);
        font-size: 2rem;
        line-height: 1;
        font-weight: 800;
    }

    .registered-list {
        display: grid;
        gap: 8px;
        padding: 6px 16px 16px;
    }

    .registered-list div {
        display: grid;
        grid-template-columns: 28px 1fr auto;
        gap: 10px;
        align-items: center;
    }

    .avatar-dot {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--soft-green, #E8F5EC);
        color: var(--primary-green, #147A45);
        font-size: 0.75rem;
        font-weight: 800;
    }

    .nutrition-summary {
        display: grid;
        grid-template-columns: 68px 1fr 82px;
        gap: 14px;
        align-items: center;
        padding: 16px;
    }

    .child-photo {
        width: 62px;
        height: 62px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: var(--soft-green, #E8F5EC);
        color: var(--primary-green, #147A45);
        font-size: 1.75rem;
    }

    .nutrition-main strong {
        display: block;
        color: var(--text-main, #17221C);
        font-size: 1.05rem;
        font-weight: 700;
    }

    .nutrition-main span {
        display: block;
        color: var(--text-secondary, #758078);
        font-size: 0.85rem;
    }

    .nutrition-donut {
        width: 74px;
        height: 74px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background:
            radial-gradient(circle at center, #FFFFFF 0 48%, transparent 49%),
            conic-gradient(#20A464 calc(var(--value, 75) * 1%), #F2B84B 0 88%, #E65353 0);
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .nutrition-donut strong {
        color: var(--primary-green, #147A45);
        font-size: 0.95rem;
        font-weight: 800;
    }

    .tips-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        padding: 16px;
    }

    .tip-tile {
        min-height: 92px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px;
        border-radius: 14px;
        color: var(--text-main, #17221C);
        background: var(--soft-green, #E8F5EC);
        border: 1px solid rgba(20, 122, 69, 0.15);
    }

    .tip-tile strong {
        display: block;
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 3px;
    }

    .tip-tile span {
        display: block;
        color: var(--text-secondary, #758078);
        font-size: 0.78rem;
    }

    .tip-tile i {
        color: var(--primary-green, #147A45);
        font-size: 1.75rem;
    }

    .schedule-feature-grid,
    .gallery-feature-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .schedule-feature-card,
    .gallery-feature-card {
        min-height: 128px;
        display: flex;
        gap: 14px;
        padding: 16px;
        border: 1px solid var(--border-color, #E9EEEA);
        border-radius: 16px;
        background: var(--bg-card, #FFFFFF);
        box-shadow: var(--shadow-xs);
    }

    .schedule-feature-card strong,
    .gallery-feature-card strong {
        display: block;
        color: var(--text-main, #17221C);
        margin-bottom: 4px;
        font-weight: 700;
    }

    .schedule-feature-card span,
    .schedule-feature-card p,
    .gallery-feature-card span {
        color: var(--text-secondary, #758078);
        font-size: 0.85rem;
        line-height: 1.45;
        margin-bottom: 0;
    }

    .gallery-feature-card {
        flex-direction: column;
    }

    .gallery-feature-card i {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: var(--primary-green, #147A45);
        background: var(--soft-green, #E8F5EC);
        font-size: 1.35rem;
    }

    .ronda-alert {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 16px;
        border: 1px solid rgba(230, 83, 83, 0.25);
        border-radius: 16px;
        background: var(--color-danger-soft, #FDEEEE);
        color: #9C1F1F;
        margin-bottom: 16px;
    }

    .ronda-alert i {
        color: var(--color-danger, #E65353);
        font-size: 1.75rem;
    }

    .ronda-alert strong {
        display: block;
        color: #9C1F1F;
        font-weight: 800;
        margin-bottom: 2px;
    }

    .ronda-alert span {
        color: #B93C3C;
        font-size: 0.85rem;
    }

    .ronda-summary-card {
        min-height: 100px;
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 16px;
        border: 1px solid var(--border-color, #E9EEEA);
        border-radius: 16px;
        background: var(--bg-card, #FFFFFF);
        box-shadow: var(--shadow-card);
    }

    .ronda-summary-card i {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        color: var(--color-danger, #E65353);
        background: var(--color-danger-soft, #FDEEEE);
        font-size: 1.35rem;
    }

    .ronda-summary-card strong {
        display: block;
        color: var(--text-main, #17221C);
        font-size: 1.75rem;
        line-height: 1;
        font-weight: 800;
    }

    .ronda-summary-card span {
        color: var(--text-secondary, #758078);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .ronda-schedule-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .ronda-schedule-card {
        display: grid;
        grid-template-columns: 58px 1fr;
        gap: 14px;
        padding: 16px;
        border: 1px solid var(--border-color, #E9EEEA);
        border-radius: 16px;
        background: var(--bg-card, #FFFFFF);
        box-shadow: var(--shadow-xs);
    }

    .ronda-schedule-card.is-mine {
        border-color: var(--primary-green, #147A45);
        background: var(--soft-green, #E8F5EC);
    }

    .ronda-date {
        width: 54px;
        min-height: 54px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        color: #9C1F1F;
        background: var(--color-danger-soft, #FDEEEE);
    }

    .ronda-date strong {
        color: var(--color-danger, #E65353);
        font-size: 1.25rem;
        line-height: 1;
    }

    .ronda-date span {
        color: var(--text-secondary, #758078);
        font-size: 0.72rem;
        text-transform: uppercase;
        font-weight: 700;
    }

    .ronda-schedule-card > div:last-child strong {
        display: block;
        color: var(--text-main, #17221C);
        margin-bottom: 4px;
        font-weight: 700;
    }

    .ronda-mini-stat {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        margin: 2px;
    }

    .ronda-mini-stat.hadir {
        color: var(--color-success, #20A464);
        background: var(--color-success-soft, #E8F7F0);
        border: 1px solid rgba(32, 164, 100, 0.2);
    }

    .ronda-mini-stat.izin {
        color: var(--color-info, #3985E8);
        background: var(--color-info-soft, #EDF4FD);
        border: 1px solid rgba(57, 133, 232, 0.2);
    }

    .ronda-mini-stat.alpa {
        color: var(--color-danger, #E65353);
        background: var(--color-danger-soft, #FDEEEE);
        border: 1px solid rgba(230, 83, 83, 0.2);
    }

    .panic-panel {
        border: 1px solid rgba(230, 83, 83, 0.25) !important;
        border-radius: 16px !important;
        background: var(--bg-card, #FFFFFF) !important;
        box-shadow: var(--shadow-card) !important;
        padding: 20px !important;
    }

    .panic-panel h5 {
        color: var(--color-danger, #E65353);
        font-weight: 800;
    }

    .panic-panel p {
        color: var(--text-secondary, #758078);
        font-size: 0.85rem;
    }

    @media (max-width: 768px) {
        .page-head { flex-direction: column; }
        .product-grid { grid-template-columns: 1fr; }
        .posyandu-board, .tips-grid, .schedule-feature-grid, .gallery-feature-grid { grid-template-columns: 1fr; }
        .ronda-schedule-grid { grid-template-columns: 1fr; }
        .pos-wide { grid-column: span 1; }
        .next-schedule-body, .nutrition-summary { grid-template-columns: 1fr; }
        .surat-history-item { flex-direction: column; }
        .child-card-head { flex-direction: column; }
    }
</style>
