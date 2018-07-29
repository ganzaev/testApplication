<?php

namespace AppBundle\Controller;

use AppBundle\Service\DownloadImageService;
use AppBundle\Service\UploadImageService;
use Doctrine\DBAL\Driver\AbstractDriverException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @var UploadImageService
     */
    private $imageService;
    /**
     * @var DownloadImageService
     */
    private $downloadImageService;

    /**
     * DefaultController constructor.
     * @param UploadImageService $imageService
     * @param DownloadImageService $downloadImageService
     */
    public function __construct(
        UploadImageService $imageService,
        DownloadImageService $downloadImageService
    )
    {
        $this->imageService = $imageService;
        $this->downloadImageService = $downloadImageService;
    }

    /**
     * @Route("upload_image/", name="uploadImage")
     * @param Request $request
     * @Method("POST")
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        try {
            $file = $request->files->get('image', null);
            if (!$file) {
                throw new \Exception('File does not exist');
            }
            $result = $this->imageService->handleIncomeImage($file);
        } catch (\Exception $ex) {
            $result = $ex->getMessage();
        }

        return new JsonResponse($result, 200);
    }

    /**
     * @param Request $request
     * @Route("download_image/", name="downloadImage")
     * @Method("GET")
     * @return Response
     */
    public function downloadAction(Request $request)
    {
        $imageName = $request->get('name', null);
        $imageWigth = $request->get('wight', null);
        $imageHeight = $request->get('height', null);
        try {
            if (!(isset(
                $imageName,
                $imageWigth,
                $imageHeight
            ))) {
                throw new \Exception('Wrong data');
            }
            $thumbnail = $this->downloadImageService->fetchImage($imageName, $imageWigth, $imageHeight);
            imagejpeg($thumbnail, 'tempImage.jpg');
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 500);
        }
        $response = new Response();
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $img);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setContent(file_get_contents('tempImage.jpg'));
        return $response;
    }
}
