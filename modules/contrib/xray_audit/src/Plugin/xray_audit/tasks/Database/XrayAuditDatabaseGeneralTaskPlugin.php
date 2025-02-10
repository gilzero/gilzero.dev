<?php

namespace Drupal\xray_audit\Plugin\xray_audit\tasks\Database;

/**
 * Plugin implementation of queries_data_node.
 *
 * @XrayAuditTaskPlugin (
 *   id = "database_general",
 *   label = @Translation("Database information"),
 *   description = @Translation("Database information."),
 *   group = "database",
 *   sort = 1,
 *   local_task = 1,
 *   operations = {
 *      "summary" = {
 *           "label" = "Summary",
 *           "description" = "Summary."
 *        },
 *      "table_size" = {
 *          "label" = "Tables",
 *          "description" = ""
 *       },
 *    },
 * )
 */
class XrayAuditDatabaseGeneralTaskPlugin extends XrayAuditDatabaseTaskPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDataOperationResult(string $operation = '') {
    switch ($operation) {
      case 'table_size':
        $data = $this->tableSize();
        break;

      case 'summary':
        $data = $this->summary();
        break;
    }

    return $data;
  }

  /**
   * Get data for operation "summary".
   *
   * @return array
   *   Render array.
   */
  protected function summary() {
    $tables = $this->getInformationAboutTables();

    // Number of tables.
    $num_tables = count($tables);

    // Total size of the database.
    $total_size = 0;
    foreach ($tables as $table) {
      $total_size += $table['total_size'];
    }

    // Get the 20 first elements of the array tables.
    $largest_tables = array_slice($tables, 0, 20);
    foreach ($largest_tables as &$largest_table) {
      $largest_table = [
        'name' => $largest_table['name'],
        'rows' => $largest_table['rows'],
        'total_size' => $largest_table['total_size'],
      ];
    }

    return [
      'table_number' => $num_tables,
      'total_size'  => $total_size,
      'largest_tables' => $largest_tables,
    ];

  }

  /**
   * Get data for operation "table_size".
   *
   * @return array
   *   Render array.
   */
  protected function tableSize() {
    $headerTable = [
      $this->t('Name'),
      $this->t('Number of Rows'),
      $this->t('Data length (MB)'),
      $this->t('Index length (MB)'),
      $this->t('Total length (MB)'),
    ];

    $resultTable = $this->getInformationAboutTables();

    return [
      'header_table' => $headerTable,
      'results_table' => $resultTable,
    ];
  }

  /**
   * Get information about tables.
   *
   * @return array
   *   Render array.
   */
  protected function getInformationAboutTables(): array {
    $tables = $this->database->query("SHOW TABLE STATUS")->fetchAll();
    $table_info = [];
    foreach ($tables as $table) {
      $data_size = $this->transformFromBytesToMb($table->Data_length);
      $index_size = $this->transformFromBytesToMb($table->Index_length);
      $table_info[] = [
        'name' => $table->Name,
        'rows' => $table->Rows,
        'size' => $data_size,
        'index_size' => $index_size,
        'total_size' => $data_size + $index_size,
      ];
    }

    usort($table_info, function ($a, $b) {
      return $b['total_size'] <=> $a['total_size'];
    });

    return $table_info;
  }

  /**
   * Transform bytes to MB.
   *
   * @param $bytes
   *   Bytes.
   *
   * @return float
   *   MB.
   */
  protected function transformFromBytesToMb($bytes) {
    if (empty($bytes)) {
      return 0;
    }
    $mg = (int) $bytes / (1024 * 1024);
    // I to ensure that the number is rounded to two decimal places.
    return round($mg, 2);
  }

  /**
   * {@inheritdoc}
   */
  public function buildDataRenderArray(array $data, string $operation = '') {
    if ($operation === 'table_size') {
      return parent::buildDataRenderArray($data, $operation);
    }

    $build = [];

    // Section: Database Properties Header.
    $build['database_properties_header'] = [
      '#markup' => '<h4>' . $this->t('Database properties') . '</h4>',
    // Restrict allowed HTML tags for security.
      '#allowed_tags' => ['h4'],
    ];

    // Section: Database Properties Content.
    $build['database_properties_content'] = [
      '#theme' => 'item_list',
      '#items' => [
        $this->t('Number of tables: @num_tables', ['@num_tables' => $data['table_number']]),
        $this->t('Total size of the database (MB): @total_size', ['@total_size' => $data['total_size']]),
      ],
      '#attributes' => ['class' => ['database-properties-list']],
    ];

    // Section: Largest Tables Header.
    $build['largest_tables_header'] = [
      '#markup' => '<h4>' . $this->t('The 20 largest tables') . '</h4>',
      '#allowed_tags' => ['h4'],
    ];

    // Section: Largest Tables Table.
    $build['largest_tables'] = [
      '#theme' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Number of Rows'),
        $this->t('Size (MB)'),
      ],
      '#rows' => $data['largest_tables'],
      '#attributes' => ['class' => ['largest-tables']],
    ];

    return $build;
  }

}
