<script>
  import { createEventDispatcher, getContext } from 'svelte';
  import FilterApplied from './FilterApplied.svelte';
  import BooleanFilter from './BooleanFilter.svelte';
  import MultipleChoiceFilter from '../MultipleChoiceFilter.svelte';
  import SearchSort from './SearchSort.svelte';
  import { FULL_MODULE_PATH, DARK_COLOR_SCHEME } from '../constants';

  const { Drupal } = window;
  const dispatch = createEventDispatcher();
  const stateContext = getContext('state');
  const sort = getContext('sort');
  const filters = getContext('filters');
  const searchString = getContext('searchString');

  export let refreshLiveRegion;
  export const filter = (row, text) =>
    Object.values(row).filter(
      (item) =>
        item && item.toString().toLowerCase().indexOf(text.toLowerCase()) > 1,
    ).length > 0;
  export let index = -1;
  export let searchText;
  searchString.subscribe((value) => {
    searchText = value;
  });

  export let filterDefinitions;
  export let sorts;

  if (!($sort in sorts)) {
    // eslint-disable-next-line prefer-destructuring
    $sort = Object.keys(sorts)[0];
  }
  let sortText = sorts[$sort];
  let filterComponent;

  export function toSearchParams() {
    const params = new URLSearchParams();

    Object.entries(filterDefinitions).forEach(([name, definition]) => {
      const { _type } = definition;
      let value = $filters[name];

      if (_type === 'boolean') {
        // Convert to '1' or '0'.
        value = Number(value).toString();
      }
      params.set(name, value);
    });
    return params;
  }

  export async function onSearch(event) {
    const state = stateContext.getState();
    const detail = {
      originalEvent: event,
      filter,
      index,
      searchText,
      page: state.page,
      pageIndex: state.pageIndex,
      pageSize: state.pageSize,
      rows: state.filteredRows,
    };
    dispatch('search', detail);
    if (detail.preventDefault !== true) {
      if (detail.searchText.length === 0) {
        stateContext.setRows(state.rows);
      } else {
        stateContext.setRows(
          detail.rows.filter((r) => detail.filter(r, detail.searchText, index)),
        );
      }
      stateContext.setPage(0, 0);
    } else {
      stateContext.setRows(detail.rows);
    }
    refreshLiveRegion();
  }

  const onAdvancedFilter = async (event) => {
    if (event) {
      const filterName = event.target.name;

      if (filterDefinitions[filterName]._type === 'boolean') {
        $filters[filterName] = event.target.value === 'true';
      } else {
        $filters[filterName] = event.target.value;
      }
    }

    const state = stateContext.getState();
    const detail = {
      originalEvent: event,
      page: state.page,
      pageIndex: state.pageIndex,
      pageSize: state.pageSize,
      rows: state.filteredRows,
    };
    dispatch('advancedFilter', detail);
    stateContext.setPage(0, 0);
    stateContext.setRows(detail.rows);
    refreshLiveRegion();
  };

  function onSelectCategory(event) {
    const state = stateContext.getState();
    const detail = {
      originalEvent: event,
      category: $filters.categories,
      page: state.page,
      pageIndex: state.pageIndex,
      pageSize: state.pageSize,
      rows: state.filteredRows,
    };
    dispatch('selectCategory', detail);
    stateContext.setPage(0, 0);
    stateContext.setRows(detail.rows);
  }

  function clearText() {
    $searchString = '';
    onSearch();
    document.getElementById('pb-text').focus();
  }

  function refresh() {
    onAdvancedFilter();
    onSelectCategory();
  }

  /**
   * Sets all filters to a falsy value.
   *
   * After this is called, hasFilterValues() will return false.
   */
  function clearFilters() {
    Object.entries(filterDefinitions).forEach(([name, definition]) => {
      const { _type } = definition;

      if (_type === 'boolean') {
        $filters[name] = false;
      } else if (_type === 'multiple_choice') {
        $filters[name] = [];
      } else {
        $filters[name] = null;
      }
    });
    refresh();
  }

  /**
   * Resets the filters to the initial values provided by the source.
   *
   * After calling this, hasUserAppliedFilters() will return false.
   */
  function resetFilters() {
    Object.entries(filterDefinitions).forEach(([name, definition]) => {
      $filters[name] = definition.value;
    });
    refresh();
  }
</script>

<form class="search__form-container">
  <div
    class="search__bar-container search__form-item js-form-item form-item js-form-type-textfield form-type--textfield"
    role="search"
  >
    <label for="pb-text" class="form-item__label">{Drupal.t('Search')}</label>
    <div class="search__search-bar">
      <input
        class="search__search_term form-text form-element form-element--type-text"
        type="search"
        id="pb-text"
        name="text"
        bind:value={$searchString}
        on:keydown={(e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            onSearch(e);
          }
          if (e.key === 'Escape') {
            e.preventDefault();
            clearText();
          }
        }}
      />
      {#if $searchString}
        <button
          class="search__search-clear"
          id="clear-text"
          type="button"
          on:click={clearText}
          aria-label={Drupal.t('Clear search text')}
          tabindex="-1"
        >
          <img
            src="{FULL_MODULE_PATH}/images/cross{DARK_COLOR_SCHEME
              ? '--dark-color-scheme'
              : ''}.svg"
            alt=""
          />
        </button>
      {/if}
      <button
        class="search__search-submit"
        type="button"
        on:click={onSearch}
        aria-label={Drupal.t('Search')}
      >
        <img
          class="search__search-icon"
          id="search-icon"
          src="{FULL_MODULE_PATH}/images/search-icon{DARK_COLOR_SCHEME
            ? '--dark-color-scheme'
            : ''}.svg"
          alt=""
        />
      </button>
    </div>
  </div>
  {#if Object.keys(filterDefinitions).length !== 0}
    <div class="search__form-filters-container">
      <div class="search__form-filters">
        {#each Object.entries(filterDefinitions) as [name, filter]}
          {#if filter._type === 'boolean'}
            <BooleanFilter
              definition={filter}
              {name}
              changeHandler={onAdvancedFilter}
            />
          {:else if filter._type === 'multiple_choice'}
            <MultipleChoiceFilter
              {name}
              filterList={Object.keys(filterDefinitions)}
              choices={filter.choices}
              on:selectCategory={onSelectCategory}
              bind:this={filterComponent}
            />
          {/if}
        {/each}
      </div>
      <div
        class="search__form-sort js-form-item js-form-type-select form-type--select js-form-item-type form-item--type"
      >
        <section
          class="search__filters"
          aria-label={Drupal.t('Search results')}
        >
          <div class="search__results-count">
            {#if 'categories' in $filters}
              {#each $filters.categories as category}
                <FilterApplied
                  label={filterDefinitions.categories.choices[category]}
                  clickHandler={() => {
                    $filters.categories.splice(
                      $filters.categories.indexOf(category),
                      1,
                    );
                    $filters.categories = $filters.categories;
                    onSelectCategory();
                  }}
                />
              {/each}
            {/if}

            <button
              class="search__filter-button"
              type="button"
              on:click|preventDefault={() => clearFilters()}
            >
              {Drupal.t('Clear filters')}
            </button>
            <button
              class="search__filter-button"
              type="button"
              on:click|preventDefault={() => resetFilters()}
            >
              {Drupal.t('Recommended filters')}
            </button>
          </div>
        </section>
        <SearchSort on:sort bind:sortText refresh={refreshLiveRegion} {sorts} />
      </div>
    </div>
  {/if}
</form>
