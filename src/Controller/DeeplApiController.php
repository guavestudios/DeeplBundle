<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Controller;

use Guave\DeeplBundle\Api\DeeplApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeeplApiController extends AbstractController
{
    protected DeeplApi $deeplApi;

    public function __construct(DeeplApi $deeplApi)
    {
        $this->deeplApi = $deeplApi;
    }

    /**
     * @Route("/api/deepl/translate", name="api_deepl_translate")
     */
    public function translate(Request $request): Response
    {
        $texts = $request->get('texts');
        $targetLang = $request->get('targetLang');
        $sourceLang = $request->get('sourceLang');

        if (empty($texts)) {
            return $this->sendError('no texts');
        }
        if (empty($targetLang)) {
            return $this->sendError('no targetLang');
        }
        if (empty($sourceLang)) {
            return $this->sendError('no sourceLang');
        }

        $translatedTexts = [];

        // fix keys with uncompleted array
        foreach ($texts as $k => $text) {
            if (str_contains($k, '[')) {
                if (!str_ends_with($k, ']')) {
                    unset($texts[$k]);
                    $texts[$k . ']'] = $text;
                }
            }
        }

        try {
            foreach ($texts as $k => $text) {
                $response = $this->deeplApi->translate($text, $sourceLang, $targetLang);
                $translatedTexts[$k] = [
                    'source' => $text,
                    'translation' => $response['translations'][0]['text'],
                ];
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return new JsonResponse(['translations' => $translatedTexts], Response::HTTP_OK);
    }

    private function sendError($msg): JsonResponse
    {
        $response = [];
        $response['status'] = false;
        $response['error'] = $msg;

        return new JsonResponse(['status' => true, 'response' => $response], Response::HTTP_OK);
    }
}
