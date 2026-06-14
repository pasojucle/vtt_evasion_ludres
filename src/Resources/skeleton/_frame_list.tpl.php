<turbo-frame id="<?= strtolower($entity_name) ?>-list" data-with-skeleton="true">
    {% include "components/list/_list_skeleton.html.twig" with {title: "<?= ucfirst($entity_name) ?>s"} %}
    <div class="block [turbo-frame[busy]>&]:hidden transition-opacity duration-200">
        <div class="border border-border rounded-md">
            <div class="bg-slate-300 uppercase px-4 py-2 text-white text-sm">
                <?= ucfirst($entity_name) ?>s - <span class="">{{ list.paginator.total }}
            </div>
            <ul id="<?= strtolower($entity_name) ?>s_container">
                {% for item in list.items %}
                    <li class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] group relative items-center gap-x-4 gap-y-2 p-3 pr-6 text-foreground lg:text-foreground/60 lg:hover:text-foreground bg-slate-100 hover:bg-slate-200 transition-colors not-last:border-b not-last:border-border">
                        <a href="{{ item.url }}" class="absolute inset-0 z-10 mr-10" title="Voir l'élément" data-turbo-frame="_top">
                            <span class="sr-only">Voir l'élément</span>
                        </a>
                        {% include 'components/list/_list_item.html.twig' with {
                            item: item, 
                            grid_template_labels: 'grid-cols-[80px_auto]', 
                            grid_template_badges: 'grid-cols-[80px_auto_40px]'
                            } %} 
                        {% include 'components/_dropdown.html.twig' with {dropdown: item.dropdown} %}
                    </li>
                {% endfor %}
            </ul>
            {% if list.items is empty %}
                {% include "components/_empty.html.twig" with {icon: 'lucide:circle-x', message: 'Aucun résultat'} %}
            {% endif %}
        </div>
        {% include 'components/pagination.html.twig' with {'paginator': list.paginator, 'margin': 'top'} %}
    </div>
</turbo-frame>