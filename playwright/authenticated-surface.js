const routes = [
  {
    id: 'add-accession',
    group: 'add',
    path: '/accession/add',
  },
  {
    id: 'add-description',
    group: 'add',
    path: '/informationobject/add',
  },
  {
    id: 'add-authority',
    group: 'add',
    path: '/actor/add',
  },
  {
    id: 'add-repository',
    group: 'add',
    path: '/repository/add',
  },
  {
    id: 'add-term',
    group: 'add',
    path: '/term/add',
  },
  {
    id: 'add-function',
    group: 'add',
    path: '/function/add',
  },
  {
    id: 'manage-accessions',
    group: 'manage',
    path: '/accession/browse',
  },
  {
    id: 'manage-donors',
    group: 'manage',
    path: '/donor/browse',
  },
  {
    id: 'manage-jobs',
    group: 'manage',
    path: '/jobs/browse',
  },
  {
    id: 'manage-physical-objects',
    group: 'manage',
    path: '/physicalobject/browse',
  },
  {
    id: 'manage-rightsholders',
    group: 'manage',
    path: '/rightsholder/browse',
  },
  {
    id: 'manage-taxonomies',
    group: 'manage',
    path: '/taxonomy/browse',
    expectedPath: '/taxonomy/list',
  },
  {
    id: 'import-xml',
    group: 'import',
    path: '/object/importSelect?type=xml',
  },
  {
    id: 'import-csv',
    group: 'import',
    path: '/object/importSelect?type=csv',
  },
  {
    id: 'validate-csv',
    group: 'import',
    path: '/object/validateCsv',
  },
  {
    id: 'import-skos',
    group: 'import',
    path: '/sfSkosPlugin/import',
  },
  {
    id: 'admin-users',
    group: 'admin',
    path: '/user/list',
  },
  {
    id: 'admin-groups',
    group: 'admin',
    path: '/aclGroup/list',
  },
  {
    id: 'admin-static-pages',
    group: 'admin',
    path: '/staticpage/list',
  },
  {
    id: 'admin-menus',
    group: 'admin',
    path: '/menu/list',
  },
  {
    id: 'admin-plugins',
    group: 'admin',
    path: '/sfPluginAdminPlugin/plugins',
  },
  {
    id: 'admin-themes',
    group: 'admin',
    path: '/sfPluginAdminPlugin/themes',
  },
  {
    id: 'admin-settings',
    group: 'admin',
    path: '/settings/global',
  },
  {
    id: 'admin-description-updates',
    group: 'admin',
    path: '/search/descriptionUpdates',
  },
  {
    id: 'admin-global-replace',
    group: 'admin',
    path: '/search/globalReplace',
    fixme: 'Returns HTTP 500 in the current test stack',
  },
  {
    id: 'admin-visible-elements',
    group: 'admin',
    path: '/settings/visibleElements',
  },
  {
    id: 'settings-clipboard',
    group: 'settings',
    path: '/settings/clipboard',
  },
  {
    id: 'settings-csv-validator',
    group: 'settings',
    path: '/settings/csvValidator',
  },
  {
    id: 'settings-page-elements',
    group: 'settings',
    path: '/settings/pageElements',
  },
  {
    id: 'settings-template',
    group: 'settings',
    path: '/settings/template',
  },
  {
    id: 'settings-diacritics',
    group: 'settings',
    path: '/settings/diacritics',
  },
  {
    id: 'settings-digital-object-derivatives',
    group: 'settings',
    path: '/settings/digitalObjectDerivatives',
  },
  {
    id: 'settings-dip-upload',
    group: 'settings',
    path: '/settings/dipUpload',
  },
  {
    id: 'settings-finding-aid',
    group: 'settings',
    path: '/settings/findingAid',
  },
  {
    id: 'settings-global',
    group: 'settings',
    path: '/settings/global',
  },
  {
    id: 'settings-header',
    group: 'settings',
    path: '/settings/header',
  },
  {
    id: 'settings-languages',
    group: 'settings',
    path: '/settings/language',
  },
  {
    id: 'settings-identifiers',
    group: 'settings',
    path: '/settings/identifier',
  },
  {
    id: 'settings-inventory',
    group: 'settings',
    path: '/settings/inventory',
  },
  {
    id: 'settings-markdown',
    group: 'settings',
    path: '/settings/markdown',
  },
  {
    id: 'settings-permissions',
    group: 'settings',
    path: '/settings/permissions',
  },
  {
    id: 'settings-privacy-notification',
    group: 'settings',
    path: '/settings/privacyNotification',
  },
  {
    id: 'settings-security',
    group: 'settings',
    path: '/settings/security',
  },
  {
    id: 'settings-site-information',
    group: 'settings',
    path: '/settings/siteInformation',
  },
  {
    id: 'settings-treeview',
    group: 'settings',
    path: '/settings/treeview',
  },
  {
    id: 'settings-uploads',
    group: 'settings',
    path: '/settings/uploads',
  },
  {
    id: 'settings-interface-labels',
    group: 'settings',
    path: '/settings/interfaceLabel',
  },
  {
    id: 'settings-analytics',
    group: 'settings',
    path: '/settings/analytics',
  },
]

const workflows = [
  { id: 'workflow-login' },
  { id: 'workflow-logout' },
  { id: 'workflow-authority-crud' },
  { id: 'workflow-repository-crud' },
  { id: 'workflow-accession-crud' },
  { id: 'workflow-global-setting' },
  { id: 'workflow-editor-boundary' },
]

module.exports = {
  routes,
  surface: [...routes, ...workflows],
  workflows,
}
