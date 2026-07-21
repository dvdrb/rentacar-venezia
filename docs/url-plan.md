# URL plan

## Preserve

- Existing vehicle URLs remain `/cars/{vehicle-slug}/`; the `cars` archive stays
  disabled.
- The legacy important URLs documented in `url-inventory.csv` require redirect
  decisions before any production migration.

## New catalogue

The new catalogue is a translated WordPress page assigned to the **Fleet
catalogue** page template. This avoids changing the `cars` archive registration
or flushing rewrites. The owner must choose the final public page slug and map
the corresponding WPML translations before activation.

Filter values are ordinary GET parameters. Pagination is server-rendered;
canonical and indexation policy will be added with the multilingual SEO phase.
