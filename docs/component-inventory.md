# Component inventory

All components are server-rendered first and use the existing WordPress/WPML
data model. Components must not contain pricing or enquiry business rules.

| Component | Purpose | Status | Data source |
| --- | --- | --- | --- |
| Global header | Navigation, language selector, WhatsApp action | Planned | Menus, WPML |
| Mobile navigation | Keyboard-operable compact navigation | Planned | Same menu data |
| Breadcrumbs | Hierarchy without hard-coded IDs | Planned | Current queried object |
| Notice | Manual-availability and estimate clarification | Planned | Static approved copy |
| Trip form | Collect trip preferences, not availability | Planned | Request state only |
| Vehicle card | Catalogue/related vehicle summary | Planned | `Rentacar_Core_Vehicle` |
| Vehicle gallery | Responsive vehicle imagery | Planned | Featured image + ACF gallery |
| Specification list | Transmission, passengers, doors, air conditioning | Planned | `Rentacar_Core_Vehicle` |
| Price summary | Indicative starting price / estimate | Planned | Core pricing service |
| FAQ accordion | Accessible frequently asked questions | Planned | Existing FAQ content |
| CTA banner | Route to WhatsApp/request flow | Planned | Approved configuration |
| Footer | Important links, contact and legal navigation | Planned | Menus/options only |

The global shell, card and notice components are the first implementation
priority. The legacy enquiry modal is reference material only and will not be
embedded in the redesigned theme.
