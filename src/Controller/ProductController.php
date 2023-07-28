<?php
namespace App\Controller;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Model\DataObject;
use Pimcore\Model\Asset;
use Pimcore\Db;
class ProductController extends FrontendController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        try{
                $listing =  new DataObject\Product\Listing();
                $listing->setUnpublished(false);
                // The $listing object is then loaded to get the product data.
                $listing        = $listing->load();
                // Two arrays, $workflowState and $status, are initialized. These arrays will store data related to workflow states and statuses for each product.
                $workflowState  =[];
                $status         =[];
                foreach($listing as $workflowState)
                {
                    $proddata[] = $workflowState->getId();
                    $db         = Db::get();
                    $query      ="Select * from element_workflow_state where cid=".$workflowState->getId();
                    $stmt       = $db->prepare($query);
                    $stmt->execute();
                    if ($stmt instanceof Result) {
                        $checkSatus =$stmt->fetchAssociative();
                        $status[$workflowState->getId()]=$checkSatus['place'];
                    }
                }
                return $this->render('product/listdata.html.twig',[
                    'entries' => $listing,
                ]);
            }
            // If any exceptions occur during the execution of the code, the catch block handles them and returns a JSON response with an error message and the line number where the exception occurred.
            catch (\Exception $ex) {
                return new JsonResponse([
                    'msg' => $ex->getMessage(),
                    'line' => $ex->getLine()
                ]);
            }
    }
    public function add(Request $request): Response
    {
        return $this->render('product/add.html.twig');
    }
    
    // for adding a new product after submitting the data form in add.twig
    public function create(Request $request): Response
    {
        try {
                $name  = $request->get('name');
                $color = $request->get('color');
                $category = $request->get('category');
                $uploadedImage      = $request->files->get('image');
                $distinationPath    = $this->getParameter('kernel.project_dir'). '/public/var/assets/';
                $originalFileName   = pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME);
                $newFileName        = $originalFileName.'-'.uniqid().'.'.$uploadedImage->guessExtension();
                $uploadedImage->move($distinationPath,$newFileName);
                $originalFullFileNamePath = $distinationPath.$newFileName;
                // $asset =  new \Pimcore\Model\Asset();
                $asset =  new Asset();
                $asset->setParentId(1);
                $asset->setFilename($newFileName);
                $asset->setData(file_get_contents($originalFullFileNamePath));
                $asset->save();
                $latestAssetsId = $asset->getId();
                $image          = Asset\Image::getById($latestAssetsId);    
                $newObject = new DataObject\Product();
                $newObject->setKey(trim($name));
                $newObject->setParent(DataObject\Folder::getByPath("/Product"));
                $newObject->setName($name);
                $newObject->setColor($color);
                $newObject->setCategory($category);
                $newObject->setImage($image);
                $newObject->setPublished(true);
                $newObject->setWorkflowState('Created');
                $newObject->save();
                return $this->redirect('/listAction');
            }catch (\Exception $ex) {
                return new JsonResponse([
                    'msg' => $ex->getMessage(),
                    'line' => $ex->getLine()
                ]);
            }
    }
    public function update(Request $request): Response
    {
        try{
                $name           = $request->get('name');
                $color          = $request->get('color');  
                $ObjectId       = $request->get('ObjectId');    
                $uploadedImage  = $request->files->get('image');
                $workflowState  = $request->get('workflowState');
                $distinationPath    = $this->getParameter('kernel.project_dir'). '/public/var/assets/';
                $originalFileName   = pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME);
                $newFileName        = $originalFileName.'-'.uniqid().'.'.$uploadedImage->guessExtension();
                $uploadedImage->move($distinationPath,$newFileName);
                $originalFullFileNamePath = $distinationPath.$newFileName;
                $asset =  new Asset();
                $asset->setParentId(1);
                $asset->setFilename($newFileName);
                $asset->setData(file_get_contents($originalFullFileNamePath));
                $retData        = $asset->save();
                $latestAssetsId = $retData->getId();
                $image          = Asset\Image::getById($latestAssetsId);    
                $newObject = DataObject\Product::getById($ObjectId);
                $newObject->setName($name);
                $newObject->setColor($color);
                $newObject->setImage($image);
                $newObject->setWorkflowState($workflowState);
                $newObject->save();    

                return $this->redirect('/listAction');
            }catch (\Exception $ex) {
                return new JsonResponse([
                    'msg' => $ex->getMessage(),
                    'line' => $ex->getLine()
                ]);
            }
    }  
    public function delete(Request $request): Response    
    {
        try {
            $ObjectId = $request->get('ObjectId');
            $myObject = DataObject\Product::getById($ObjectId);
            $myObject->delete();
            return new JsonResponse(['status' => 'success']);
        } catch (\Exception $ex) {
            return new JsonResponse([
                'status' => 'error',
                'msg' => $ex->getMessage(),
                'line' => $ex->getLine()
            ]);
        }
    }

    // for update
    public function getProductDataAction(Request $request): JsonResponse
    {
        $objectId = $request->get('ObjectId');
        $myObject = DataObject\Product::getById($objectId);
        if (!$myObject) {
            return new JsonResponse(['error' => 'Product not found.'], 404);
        }
        $data = [
            'name' => $myObject->getName(),
            'color' => $myObject->getColor(),
            // Add other fields you want to fetch and pre-fill in the edit form
        ];
        return new JsonResponse($data);
    }

    public function unPublishprodcutAction(Request $request)
    {
        $id = $request->query->get('id');
        $myObject = DataObject\Product::getById($id);
        $myObject->setPublished(false);
        $myObject->save();
        return $this->redirect('/listAction');
    }

    public function viewProduct(Request $request, $id): Response
    {
        // Fetch the product data based on the given $id
        $product = \Pimcore\Model\DataObject\Product::getById($id);

        // Render the view template and pass the product data
        return $this->render('product/view_product.html.twig', [
            'product' => $product,
        ]);
    }
}
?>