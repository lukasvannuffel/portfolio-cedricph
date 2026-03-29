<?php
/**
 * Template Part: Portfolio Filters
 *
 * Shared filter UI (category dropdown + search) for analog and digital portfolio pages.
 * Included via get_template_part() in page-analog.php and page-digital.php.
 */
?>
<div class="portfolio-filters" role="search" aria-label="<?php esc_attr_e('Filter projects', 'cedricph'); ?>">

    <div class="portfolio-filters__search">
        <div class="portfolio-filters__search-wrap">
            <svg class="portfolio-filters__search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
                id="portfolio-search"
                class="portfolio-filters__search-input"
                type="search"
                placeholder="<?php esc_attr_e('Search projects...', 'cedricph'); ?>"
                autocomplete="off"
                spellcheck="false"
                aria-controls="portfolio-grid"
                aria-label="<?php esc_attr_e('Search projects', 'cedricph'); ?>"
            >
            <button
                class="portfolio-filters__search-clear"
                type="button"
                aria-label="<?php esc_attr_e('Clear search', 'cedricph'); ?>"
                hidden
            >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="portfolio-filters__dropdown-wrap">
        <button
            class="portfolio-filters__dropdown-btn"
            type="button"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-controls="portfolio-filter-listbox"
        >
            <span class="portfolio-filters__dropdown-label"><?php esc_html_e('All Categories', 'cedricph'); ?></span>
            <svg class="portfolio-filters__dropdown-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>

        <div
            id="portfolio-filter-listbox"
            class="portfolio-filters__listbox"
            role="listbox"
            aria-multiselectable="true"
            aria-label="<?php esc_attr_e('Filter by category', 'cedricph'); ?>"
        >
            <ul class="portfolio-filters__options" role="presentation">
                <?php
                $filter_categories = array(
                    'portrait'   => __('Portrait', 'cedricph'),
                    'events'     => __('Events', 'cedricph'),
                    'commercial' => __('Commercial', 'cedricph'),
                );
                foreach ($filter_categories as $slug => $label) :
                ?>
                <li
                    class="portfolio-filters__option"
                    role="option"
                    aria-selected="false"
                    data-value="<?php echo esc_attr($slug); ?>"
                    tabindex="0"
                >
                    <span class="portfolio-filters__option-check" aria-hidden="true"></span>
                    <span class="portfolio-filters__option-label"><?php echo esc_html($label); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div
        class="portfolio-filters__active-tags"
        aria-live="polite"
        aria-label="<?php esc_attr_e('Active filters', 'cedricph'); ?>"
    ></div>

</div>
