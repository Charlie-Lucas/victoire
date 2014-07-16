
# Architecture

CoreBundle

Une page contient un widget map.

Un widget map est calculé en fonction de la page courante et du template associé.
Le template possède également un widget map calculé en fonction de son template.

Les widgets maps sont persistés en tant que tableaux.

Lorsqu'une page est chargée, ce tableau est transformé en objet et inversement.

Une page contient des slots qui contiennent des widgetMaps.

Un widgetMap référence un widget.

## Affichage d'une page

Le processus d'affichage d'une page est le suivant:
* On récupère la page
* Si la page est un businessEntityTemplate, un récupère l'instance de l'entité
* On affichage le twig de la page qui fait un cmsSlot
* L'extension twig du core bundle récupère le widgetMap calculé.
* Pour chacun des widgets, on demande au widgetManager d'afficher ce widget
* Le widgetManager appele le widgetManager qui s'occupe du widget.
* En fonction du mode d'affichage, le widget utilise une fonction (renderStatic, renderBusinessEntity, etc.)
* La vue du widget s'occupe d'afficher le widget


# Le widget Map

Un widget map possède une action, un identifiant de widget et une position (ainsi qu'une référence).

Il peut créer/remplacer/supprimer un widget.

Le widget map d'une page étant un calcul entre le widget map de la page et du template, nous avons donc des actions.

Create: Ce widget est ajouté
Replace: Le widget parent est remplacé par ce widget
Remove: Le widget parent est supprimé. L'interface ne permet de revenir en arrière sur cette action.

Un widget map contenant un id dont le widget est inexistant provoquera l'affichage d'une erreur.

Une commande permet de supprimer les widgetMap liés à des widgets qui n'existent pas. Elle permet un nettoyage du widgetMap.


## Les businessEntity

Un businessEntity est une entité taguée avec l'annotation VicBusinessEntity

Un businessEntity possède des businessProperty.

Les businessEntity sont chargés à partir d'un tableau qui est dans le cache.

Ce tableau est lui même créé en fonction des annotations sur les entité.


## Les businessEntityTemplate

Un businessEntityTemplate est un template permettant d'instancier de manière dynamique une entité.

Lors de la création d'un businessEntityTemplate, l'url doit contenir au moins une businessProperty de type businessIdentifier. On peut également ajouter des attributs de type "seoable" dans l'url mais cela n'a aucun impact sur la recherche de l'entité.

Ce businessIdentifier permet d'identifier un businessEntity.

Le titre du businessEntityTemplate permet d'utiliser des attributs de type "seoable". Lorsque le businessEntityTemplate est affiché avec une instance de l'entité, le titre est mis à jour.

### Les businessIdentifier

Un businessEntity doit avoir au moins un businessIdentifier pour pouvoir être affiché dans une businessEntityTemplate.

Cettre attribut peut être un id ou un slug ou un quelconque attribut.

### Le SEO

Les paramêtres du SEO d'un businessEntityTemplate sont éditables comme n'importe quelle page.

On peut toutefois utiliser des paramêtres de l'entité courante en utilisant une chaine de caractères spécifique.
Les paramêtres sont utilisable sous la forme {{item.name}}

Les attributs utilisables sont ceux annotés en tant "seoable".


# Les widgets

Une commande permet de générer un widget: victoire:generate:widget

Un widget doit se trouver dans le namespace Victoire/Widget.

Un manager est automatiquement créé. Ce manager hérite de baseManager.

Vous pouvez réécrire ici toutes les fonctions qui sont a personnaliser pour ce widget.

 *  getWidgetStaticContent
 *  getWidgetBusinessEntityContent
 *  getWidgetEntityContent
 *  getWidgetQueryContent


# Le sitemap

Les liens du sitemap en bleue clair sont des liens générés automatiquement.

Ils concernent des businessEntityTemplate.


