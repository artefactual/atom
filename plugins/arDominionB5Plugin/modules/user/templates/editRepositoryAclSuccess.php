<?php

echo get_partial('aclGroup/aclRepository', [
    'resource' => $resource,
    'basicActions' => $basicActions,
    'repositories' => $repositories,
    'form' => $form,
]);
