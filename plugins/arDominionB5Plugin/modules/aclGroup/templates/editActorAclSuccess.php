<?php

echo get_partial('aclGroup/aclActor', [
    'resource' => $resource,
    'basicActions' => $basicActions,
    'actors' => $actors,
    'form' => $form,
]);
