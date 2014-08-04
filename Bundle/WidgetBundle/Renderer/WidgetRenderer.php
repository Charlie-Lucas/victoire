<?php
namespace Victoire\Bundle\WidgetBundle\Renderer;

use Symfony\Component\DependencyInjection\Container;
use Victoire\Bundle\CoreBundle\Event\WidgetRenderEvent;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\WidgetBundle\Model\Widget;

class WidgetRenderer
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * render the WidgetRedactor
     * @param WidgetRedactor $widget
     * @param Entity         $entity
     *
     * @return widget show
     */
    public function render(Widget $widget, $entity = null)
    {
        //the mode of display of the widget
        $mode = $widget->getMode();

        //if entty is given and it's not the object, retrive it and set the entity for the widget
        if (!is_object($entity) && method_exists($widget->getPage(), 'getBusinessEntityName')) {

            $entityNamespaces = $this->container->get('victoire_core.annotation_reader')->getBusinessClasses();
            $entityNamespace = $entityNamespaces[$widget->getPage()->getBusinessEntityName()];
            $entity = $this->container->get('doctrine.orm.entity_manager')->getRepository($entityNamespace)->findOneById($entity);
        }
        $widget->setEntity($entity);

        //the templating service
        $templating = $this->container->get('victoire_templating');

        //the content of the widget
        $content = $this->container->get('victoire_widget.widget_content_resolver')->getWidgetContent($widget);

        //the template displayed is in the widget bundle
        $templateName = $this->container->get('victoire_widget.widget_helper')->getTemplateName('show', $widget);

        return $templating->render(
            $templateName,
            array(
                "widget" => $widget,
                "content" => $content
            )
        );
    }


    /**
     * @todo should reside in a WidgetRenderer class
     * render a widget
     *
     * @param Widget  $widget
     * @param boolean $addContainer
     * @param Entity  $entity
     *
     * @return template
     */
    public function renderContainer(Widget $widget, $addContainer = false, $entity = null)
    {
        $html = '';
        $dispatcher = $this->container->get('event_dispatcher');
        $securityContext = $this->container->get('security.context');

        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_PRE_RENDER, new WidgetRenderEvent($widget, $html));

        $html .= $this->render($widget, $entity);

        if ($securityContext->isGranted('ROLE_VICTOIRE')) {
            $html .= $this->renderActions($widget->getSlot(), $widget->getPage());
        }

        if ($addContainer) {
            $html = "<div class='vic-widget-container' data-id=\"".$widget->getId()."\" id='vic-widget-".$widget->getId()."-container'>".$html.'</div>';
        }

        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_POST_RENDER, new WidgetRenderEvent($widget, $html));

        return $html;
    }

    /**
     * @todo should reside in a WidgetRenderer class
     * render widget actions
     * @param Widget $widget
     *
     * @return template
     */
    public function renderWidgetActions(Widget $widget)
    {
        return $this->container->get('victoire_templating')->render(
            'VictoireCoreBundle:Widget:widgetActions.html.twig',
            array(
                "widget" => $widget,
                "page" => $widget->getCurrentPage(),
            )
        );
    }

    /**
     * @todo should reside in a WidgetRenderer class
     * render slot actions
     *
     * @param string  $slot
     * @param Page    $page
     * @param boolean $first
     *
     * @return template
     */
    public function renderActions($slot, Page $page, $first = false)
    {
        $slots = $this->container->getParameter('victoire_core.slots');

        $availableWidgets = $this->container->getParameter('victoire_core.widgets');
        $widgets = array();

        //If the slot is declared in config
        if (!empty($slots[$slot]) && !empty($slots[$slot]['widgets'])) {
            //parse declared widgets
            $slotWidgets = array_keys($slots[$slot]['widgets']);
        } else {
            //parse all widgets
            $slotWidgets = array_keys($availableWidgets);
        }

        foreach ($slotWidgets as $slotWidget) {
            $widgetParams = $availableWidgets[$slotWidget];
            // if widget has a parent
            if (!empty($widgetParams['parent'])) {
                // place widget under its parent
                $widgets[$widgetParams['parent']]['children'][$slotWidget]['params'] = $widgetParams;
            } else {
                $widgets[$slotWidget]['params'] = $widgetParams;
            }
        }
        $max = null;
        if (!empty($slots[$slot]) && !empty($slots[$slot]['max'])) {
            $max = $slots[$slot]['max'];
        }

        return $this->container->get('victoire_templating')->render(
            "VictoireCoreBundle:Widget:actions.html.twig",
            array(
                "slot"    => $slot,
                "page"    => $page,
                'widgets' => $widgets,
                'max'     => $max,
                'first'   => $first,
            )
        );
    }
    /**
     * Get the extra classes for the css
     *
     * @return string The classes
     */
    public function getExtraCssClass(Widget $widget)
    {
        $cssClass = 'vic-widget-'.strtolower($this->container->get('victoire_widget.widget_helper')->getWidgetName($widget));

        return $cssClass;
    }

}
