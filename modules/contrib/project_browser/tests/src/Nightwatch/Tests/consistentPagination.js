module.exports = {
  '@tags': ['project_browser'],
  before(browser) {
    browser.drupalInstall().drupalInstallModule('project_browser_test', true);
  },
  after(browser) {
    browser.drupalUninstall();
  },
  'Test pagination consistency across tabs': function (browser) {
    browser.drupalLoginAsAdmin(() => {
      browser
        .drupalRelativeURL('/admin/modules/browse/project_browser_test_mock')
        .waitForElementVisible('h1', 100)
        .assert.textContains('h1', 'Browse projects')
        .click(
          'select[name="security_advisory_coverage"] option[value="false"]',
        )
        .click('select[name="maintenance_status"] option[value="false"]')
        .assert.visible('select.pagination__num-projects')
        .click('select.pagination__num-projects option[value="24"]');

      browser
        .openNewWindow('tab')
        .drupalRelativeURL('/admin/modules/browse/project_browser_test_mock')
        .waitForElementVisible('h1', 100)
        .assert.textContains('h1', 'Browse projects')
        .click(
          'select[name="security_advisory_coverage"] option[value="false"]',
        )
        .click('select[name="maintenance_status"] option[value="false"]')
        .assert.visible('select.pagination__num-projects')
        .getValue('select.pagination__num-projects', function (result) {
          this.assert.strictEqual(
            result.value,
            '24',
            'The selected value is correctly reflected in the second tab.',
          );
        });
    });
  },
};
