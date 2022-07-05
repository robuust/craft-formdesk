<?php

namespace robuust\formdesk\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;

/**
 * Submit controller.
 */
class SubmitController extends Controller
{
    public $allowAnonymous = true;

    /**
     * Submit form to Formdesk.
     *
     * @return Response|null
     */
    public function actionIndex(): ?Response
    {
        $this->requirePostRequest();

        $values = $this->request->getBodyParams();
        $list = $values['list_id'];

        $errors = null;
        try {
            $result = $this->module->formdesk->postAsync("forms/{$list}/results/?process_messages=true", [
                'json' => array_slice($values, 1),
            ])->wait();
        } catch (\Exception $e) {
            $errors = $e;
        }

        if ($errors) {
            if ($this->request->getAcceptsJson()) {
                return $this->asJson([
                    'errors' => $errors,
                ]);
            }

            // Send the entry back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'errors' => $errors,
            ]);

            return null;
        }

        return $this->redirectToPostedUrl((object) $result);
    }
}
