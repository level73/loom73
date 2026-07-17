# Loom73 Labels and User feedback

We place all labels for the UI in the `/config/labels.php` file.

Whenever possible (and it always should be) use the `const` keyword to set labels. 

When the label is carrying feedback for the user it should always be prepended by the __MSG__ particle. 

Labels should be descriptive enough for us to understand what kind of event/UI element we are using them for. The label naming convention should look like this:

- __TYPE__ : is this a ui label, or is it feedback?
- __ACTION/LOCATION__ : What triggers this label? Where should it live in the UI?
- __OUTCOME__ : This applies to feedback labels more than anything else. It should describe if the outcome was positive or negative (FAIL/SUCCESS)

## Examples
__MSG_QUERY_SUCCESS__ : Operation on the database was successful

__UI_NAVIGATION__ : this could be an array of navigation entries, indexed and labeled. 