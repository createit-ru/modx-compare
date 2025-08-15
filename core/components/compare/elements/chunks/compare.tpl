{* $products | print *}

<div class="compare-page">

    {if $products && count($products) > 0}

    <div class="compare-page__actions">
        {if count($products) > 1}
        <div class="compare-page__action">
            <label class="compare-page__action-differences compare-checkbox">
                <input class="compare-checkbox__input" type="checkbox" name="differences" value="1">
                <span class="compare-checkbox__text">Показывать только отличия</span>
                <span class="compare-checkbox__checkmark"></span>
            </label>
        </div>
        {/if}
        <div class="compare-page__action">
            <button name="clean" class="compare-page__action-clean js-compare-clean" data-list="{$list}">Очистить список</button>
        </div>
    </div>

    <div class="compare-grid">

        <!-- Фото -->
        <div class="compare-grid__row compare-grid__row--image">
            <div class="compare-grid__cell compare-grid__cell--param compare-grid__cell--category">
                Товары
            </div>
            <div class="compare-grid__cell compare-grid__cell--values">
                <div class="compare-specification">
                    <div class="compare-specification__row">
                        {foreach $products as $product}
                        <div class="compare-specification__cell">
                            <button class="compare-grid__remove js-compare-remove" data-id="{$product.id}" data-list="{$list}">×</button>
                            <a href="{$product.id | url}" target="_blank" class="compare-grid-image">
                                <picture>
                                    <img src="{$product.image}" alt="{$product.pagetitle | escape}" class="compare-grid-image__img" />
                                </picture>
                            </a>
                        </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>

        <!-- Название -->
        <div class="compare-grid__row compare-grid__row--title">
            <div class="compare-grid__cell compare-grid__cell--param">
                Модель
            </div>
            <div class="compare-grid__cell compare-grid__cell--values">
                <div class="compare-specification">
                    <div class="compare-specification__row">
                        {foreach $products as $product}
                        <div class="compare-specification__cell">
                            <a href="{$product.id | url}" target="_blank" class="compare-grid-product">
                                {$product.pagetitle}
                            </a>
                        </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>

        <!-- Цена -->
        {if array_key_exists('price', $fields)}
        <div class="compare-grid__row compare-grid__row--price">
            <div class="compare-grid__cell compare-grid__cell--param">
                Цена
            </div>
            <div class="compare-grid__cell compare-grid__cell--values">
                <div class="compare-specification">
                    <div class="compare-specification__row">
                        {foreach $fields['price'].values as $price}
                        {set $best = $price.best ? 'compare-specification__cell--best' : ''}
                        <div class="compare-specification__cell {$best}">
                        {if $price.raw > 0}
                            <span class="compare-specification__cell-price">{$price.formatted}</span> руб.
                        {else}
                            -
                        {/if}
                        </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
        {/if}
        

        <!-- Свойства -->
        {foreach $fields as $field => $fieldData}
        {if $field == 'price'}{continue}{/if}
        {set $different = $fieldData.different ? 'compare-grid__row--different' : 'compare-grid__row--same'}
        <div class="compare-grid__row compare-grid__row--item {$different}">
            <div class="compare-grid__cell compare-grid__cell--param">
                {$fieldData.title}
            </div>
            <div class="compare-grid__cell compare-grid__cell--values">
                <div class="compare-specification">
                    <div class="compare-specification__row">
                    {foreach $fieldData.values as $value}
                        {set $best = $value.best ? 'compare-specification__cell--best' : ''}
                        <div class="compare-specification__cell {$best}">
                            {$value.formatted}
                        </div>
                    {/foreach}
                    </div>
                </div>
            </div>
        </div>
        {/foreach}
        
    </div> <!-- .compare-grid -->

    {else}
        <p>
            Список сравнения пуст, добавьте в него товары из Каталога.
        </p>
    {/if}

</div> <!-- .compare-page -->