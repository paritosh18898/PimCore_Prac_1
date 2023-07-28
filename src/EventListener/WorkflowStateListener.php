<?php
namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Registry;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\Category;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Pimcore\Event\Model\DataObjectEvent;


class WorkflowStateListener implements EventSubscriberInterface
{
    private $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public function Approve(TransitionEvent $event)
    {
        $transition = $event->getTransition();
        $workflowState = $transition->getName();

        if ($workflowState === 'Approve') {
            $product = $event->getSubject();
            $id = $product->getId();
            $products = Product::getById($id);
            
            $products->setWorkflowState('In Review State');
            $products->save();
        }
    }

    public function rejected(TransitionEvent $event)
    {
        $transition=$event->getTransition();
        $workflowState=$transition->getName();
       
        if($workflowState === 'Reject')
        {
            $product = $event->getSubject();
            $id = $product->getId();
            $products = Product::getById($id);
            $products->setWorkflowState('Rejected');
            $products->save();
        }
    }

    public function Approved(TransitionEvent $event)
    {
        $transition=$event->getTransition();
        $workflowState=$transition->getName();
        if($workflowState === 'Approved')
        {
            $product = $event->getSubject();
            $id = $product->getId();
            $products = Product::getById($id);
            $products->setWorkflowState('Approved');
            $products->save();
        }
    }



    
    public function onProductUpdate(DataObjectEvent $event)
    {
        $object = $event->getObject();

        if ($object instanceof Product) {
            // Get the assigned category of the product
            $category = $object->getCategory();

            if ($category instanceof Category) {
                // Get the count of products in the category
                $productCount = Product::getList(['category' => $category->getId()])->count();

                // Convert the productCount to a string
                $productCountString = (string) $productCount;

                // Update the "product_count" field in the category
                $category->setProduct_count($productCountString);
                $category->save();
            }
        }
    }
    
   
    public static function getSubscribedEvents()
    {
        return [
            'workflow.product_workflow.transition.Approve'  => 'Approve',
            'workflow.product_workflow.transition.Approved' => 'Approved',
            'workflow.product_workflow.transition.Reject'   => 'rejected',
            'pimcore.dataobject.postUpdate' => 'onProductUpdate',

        ];
    }
}
?>