<?php

echo get_partial('aclGroup/aclInformationObject', [
    'resource' => $resource,
    'basicActions' => $basicActions,
    'informationObjects' => $informationObjects,
    'root' => $root,
    'repositories' => $repositories,
    'form' => $form,
]);
