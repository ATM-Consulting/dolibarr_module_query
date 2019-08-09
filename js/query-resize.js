function handleResizing()
{
	if(gridster)
	{
		// override de la largeur settée en dur par Gridster.js pour que le resize soit pris en compte.
		gridster.$el.css({ width: 'auto' });

		let widgetWidth = calculateGridsterWidth();

		gridster.min_widget_width = widgetWidth + 20; // Valeur qui tient compte des marges, d'où le + 20
		gridster.min_widget_height = cellHeight + 20; // Valeur qui tient compte des marges, d'où le + 20
		gridster.options.widget_base_dimensions[0] = widgetWidth; // Valeur qui NE tient PAS compte des marges
		gridster.options.widget_base_dimensions[1] = cellHeight; // Valeur qui NE tient PAS compte des marges

		// Cela lance tous les calculs de grille et de dimensionnement
		gridster.init();
	}

	// On retrace les éventuels graphiques
	$('[id^=div_query_chart]').each(function(index, elem)
	{
		redrawChart(elem);
	});

	// Si on est dans une iframe (dans le widget de page d'accueil ou dans les tableaux sur certaines card), on adapte la frame parente...
	let parentIframe = window.parent.document.getElementById('queryIframe');

	if(parentIframe)
	{
		// html plutôt que body pour prendre en compte certaines marges
		parentIframe.height = $('html').height();
	}
}


function redrawChart(elem)
{
	let chartID = $(elem).attr('id').replace(/^div_query_chart/, '');

	// La fonction que nous allons appeler est générée par TListviewTBS::renderChart() et fait appel à l'API Google Visualization
	let functionToCall = 'drawChart' + chartID;
	let height = 0;

	// Si on est dans un dashboard en mode édition, la heuteur peut changer => on doit recalculer la hauteur du graphique
	if(gridster)
	{
		let parent = $(elem).parents('li.gs-w').first();

		// Taille verticale en cellules dans la grille
		let sizeY = parseInt(parent.attr('data-sizey'));

		// Si une cellule prend plus d'une case, sa hauteur réelle comprend les marges
		if(sizeY > 1)
		{
			height += (sizeY - 1) * 20;
		}

		// 66 => espace pour le bouton "Voir en liste"
		height += (sizeY * gridster.options.widget_base_dimensions[1] - 66);
	}

	if(window[functionToCall])
	{
		// Attention, le passage de paramètre a été introduit dans Abricot en août 2019...
		window[functionToCall](height);
	}
}

var gridster = null;
