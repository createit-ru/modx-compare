<div class="compare compare--load" compare-root data-id="{$id}" data-list="{$list}">
    <button class="compare__button">
        <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect y="8" width="3" height="10" fill="#A6A6A6" />
            <rect x="7" width="3" height="18" fill="#A6A6A6" />
            <rect x="14" y="4" width="3" height="14" fill="#A6A6A6" />
        </svg>
        <span class="compare__button-text" data-add="В сравнение" data-remove="В сравнении">В сравнение</span>
    </button>
    <a href="{$page | url}?list={$list}" class="compare__go">Сравнить (<span class="compare__count">{$count}</span>)</a>
</div>