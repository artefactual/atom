<?php

echo get_component('aclGroup', 'termAclForm', [
    'resource' => $resource,
    'permissions' => $permissions,
    'form' => $form,
]);
