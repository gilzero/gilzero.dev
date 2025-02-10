<script>
  import {
    processQueue,
    queueList,
    updated,
    clearQueue,
  } from './QueueProcessor';
  import Loading from './Loading.svelte';
  import LoadingEllipsis from './Project/LoadingEllipsis.svelte';

  let loading = false;

  const { Drupal } = window;
  let buttonClasses;
  let bulkActionClasses = '';

  $: currentQueueList = $queueList || [];
  $: queueLength = currentQueueList.length;
  let projectsToActivate = [];
  let projectsToDownloadAndActivate = [];

  const handleClick = async () => {
    loading = true;
    await processQueue();
    loading = false;
    $updated = new Date().getTime();
  };

  function clearSelection() {
    projectsToDownloadAndActivate = [];
    projectsToActivate = [];
    clearQueue();

    $updated = new Date().getTime();
  }
  $: {
    // @see css/pb.css
    if ('gin' in drupalSettings) {
      buttonClasses = 'button--small button button--primary';
      if (drupalSettings.gin.darkmode !== '1') {
        bulkActionClasses = 'views-bulk-actions-gin';
      }
    } else {
      buttonClasses = 'install_button';
    }
  }
</script>

<div
  class=" views-bulk-actions pb-install_bulk_actions {bulkActionClasses}"
  data-drupal-sticky-vbo={queueLength !== 0}
>
  <div
    class="views-bulk-actions__item
  views-bulk-actions__item--status views-bulk-actions__item-gin"
  >
    {#if queueLength === 0}
      {Drupal.t('No projects selected')}
    {:else}
      {Drupal.formatPlural(
        queueLength,
        '1 project selected',
        '@count projects selected',
      )}
    {/if}
  </div>
  <button
    class="project__action_button install_button_common {buttonClasses}"
    on:click={handleClick}
  >
    {#if loading}
      <Loading />
      <LoadingEllipsis
        message={Drupal.formatPlural(
          queueLength,
          'Installing 1 project',
          'Installing @count projects',
        )}
      />
    {:else}
      {Drupal.t('Install selected projects')}
    {/if}
  </button>
  {#if queueLength !== 0}
    <button class="button clear_button" on:click={clearSelection}>
      {Drupal.t('Clear selection')}
    </button>
  {/if}
</div>
