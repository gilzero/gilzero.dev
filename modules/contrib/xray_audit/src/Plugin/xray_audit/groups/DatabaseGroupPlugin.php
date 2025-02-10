<?php

namespace Drupal\xray_audit\Plugin\xray_audit\groups;

use Drupal\xray_audit\Plugin\XrayAuditGroupPluginBase;

/**
 * Plugin implementation of the xray_audit_group_plugin.
 *
 * @XrayAuditGroupPlugin (
 *   id = "database",
 *   label = @Translation("Database"),
 *   description = @Translation("Database information."),
 *   sort = 40
 * )
 */
class DatabaseGroupPlugin extends XrayAuditGroupPluginBase {

}
