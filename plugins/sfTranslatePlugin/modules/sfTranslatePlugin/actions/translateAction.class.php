<?php

/*
 * This file is part of the sfTranslatePlugin package.
 * (c) 2007 Jack Bates <ms419@freezone.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * i18n actions.
 *
 * @author     Your name here
 */
class sfTranslatePluginTranslateAction extends sfAction
{
    /**
     * Executes index action.
     *
     * @param mixed $request
     */
    public function execute($request)
    {
        if (!QubitAcl::check('userInterface', 'translate')) {
            QubitAcl::forwardUnauthorized();
        }

        $user = $this->context->user;

        $error = [];
        $status = [];

        $sourceMessages = $request->getParameter('source', []);
        $targetMessages = $request->getParameter('target', []);
        foreach ($sourceMessages as $key => $sourceMessage) {
            $targetMessage = $targetMessages[$key] ?? null;

            if (
                !is_string($sourceMessage)
                || !is_string($targetMessage)
                || !$this->context->i18n->update(
                    $sourceMessage,
                    $targetMessage,
                )
            ) {
                $error[] = (string) $sourceMessage.(string) $targetMessage;
            } else {
                $status[] = $sourceMessage.$targetMessage;
            }
        }

        if (!empty($error)) {
            $this->forward($user->getAttribute('moduleName', 'default', 'sfHistoryPlugin'), $user->getAttribute('actionName', 'index', 'sfHistoryPlugin'));
        }

        if (null !== $redirectUrl = Qubit::filterRedirectTarget($request->getReferer())) {
            $this->redirect($redirectUrl);
        }

        $this->redirect('@homepage');
    }
}
