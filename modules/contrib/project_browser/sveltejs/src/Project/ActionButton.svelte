<script>
  import { PACKAGE_MANAGER, MAX_SELECTIONS } from '../constants';
  import { openPopup, getCommandsPopupMessage } from '../popup';
  import ProjectButtonBase from './ProjectButtonBase.svelte';
  import ProjectStatusIndicator from './ProjectStatusIndicator.svelte';
  import ProjectIcon from './ProjectIcon.svelte';
  import LoadingEllipsis from './LoadingEllipsis.svelte';
  import {
    processQueue,
    addToQueue,
    queueList,
    removeFromQueue,
    updated,
  } from '../QueueProcessor';

  // eslint-disable-next-line import/no-mutable-exports,import/prefer-default-export
  export let project;
  const drupalSetting = drupalSettings;

  const { Drupal } = window;
  const processMultipleProjects = MAX_SELECTIONS === null || MAX_SELECTIONS > 1;

  $: isInQueue = $queueList.some((item) => item.id === project.id);

  // If MAX_SELECTIONS is null (no limit), then the queue is never full.
  const queueFull = $queueList.length === MAX_SELECTIONS;

  let loading = false;

  function handleAddToQueueClick(singleProject) {
    addToQueue(singleProject);
    $updated = new Date().getTime();
  }

  function handleDequeueClick(projectId) {
    removeFromQueue(projectId);
    $updated = new Date().getTime();
  }

  const onClick = async () => {
    if (processMultipleProjects) {
      if (isInQueue) {
        handleDequeueClick(project.id);
      } else {
        handleAddToQueueClick(project);
      }
    } else {
      handleAddToQueueClick(project);
      loading = true;
      await processQueue();
      loading = false;
      $updated = new Date().getTime();
    }
  };
</script>

<div class="pb-actions">
  {#if !project.is_compatible}
    <ProjectStatusIndicator {project} statusText={Drupal.t('Not compatible')} />
  {:else if project.status === 'active'}
    <ProjectStatusIndicator {project} statusText={Drupal.t('Installed')}>
      {#if 'gin' in drupalSetting && drupalSetting.gin.darkmode === '1'}
        <ProjectIcon type="greenInstalled" />
      {:else}
        <ProjectIcon type="installed" />
      {/if}
    </ProjectStatusIndicator>
  {:else}
    <span>
      {#if PACKAGE_MANAGER.available && PACKAGE_MANAGER.errors.length === 0}
        {#if isInQueue && !processMultipleProjects}
          <ProjectButtonBase>
            <LoadingEllipsis />
          </ProjectButtonBase>
        {:else if queueFull && !isInQueue && processMultipleProjects}
          <ProjectButtonBase disabled>
            {@html Drupal.t(
              'Select <span class="visually-hidden">@title</span>',
              {
                '@title': project.title,
              },
            )}
          </ProjectButtonBase>
        {:else}
          <ProjectButtonBase click={onClick}>
            {#if isInQueue}
              {@html Drupal.t(
                'Deselect <span class="visually-hidden">@title</span>',
                {
                  '@title': project.title,
                },
              )}
            {:else if processMultipleProjects}
              {@html Drupal.t(
                'Select <span class="visually-hidden">@title</span>',
                {
                  '@title': project.title,
                },
              )}
            {:else}
              {@html Drupal.t(
                'Install <span class="visually-hidden">@title</span>',
                {
                  '@title': project.title,
                },
              )}
            {/if}
          </ProjectButtonBase>
        {/if}
      {:else if project.commands}
        {#if project.commands.match(/^https?:\/\//)}
          <a href={project.commands} target="_blank" rel="noreferrer"
            ><ProjectButtonBase>{Drupal.t('Install')}</ProjectButtonBase></a
          >
        {:else}
          <ProjectButtonBase
            aria-haspopup="dialog"
            click={() => openPopup(getCommandsPopupMessage(project), project)}
          >
            {@html Drupal.t(
              'View Commands <span class="visually-hidden">for @title</span>',
              {
                '@title': project.title,
              },
            )}
          </ProjectButtonBase>
        {/if}
      {/if}
    </span>
  {/if}
</div>
