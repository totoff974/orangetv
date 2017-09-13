
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
	
/*
 * COMMANDES
 */
	if (init(_cmd.name).indexOf("Mosaique ") == '-1' && init(_cmd.type) == 'action') {
		var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" disabled style="width : 140px;" placeholder="{{Nom}}">';
		tr += '</td>';
		tr += '<td>';
		if (init(_cmd.name) != 'Refresh') {
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="code_touche" disabled style="margin-bottom : 5px;" />';
		}
		tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
		}
		tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
		tr += '</td>';
		tr += '</tr>';
		$('#table_cmd tbody').append(tr);
		$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
		if (isset(_cmd.type)) {
			$('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
		}
		jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
	}
/*
 * INFO
 */	
 	if (init(_cmd.type) == 'info') {
		var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" disabled style="width : 140px;" placeholder="{{Nom}}">';
		tr += '</td>';
		tr += '<td>';
		if (init(_cmd.name) == 'Etat Decodeur') {
		 tr += '<input class="cmdAttr form-control type input-sm expertModeVisible" data-l1key="configuration" data-l2key="etat_decodeur" disabled style="margin-bottom : 5px;" />';
		}
		if (init(_cmd.name) == 'Fonction') {
		 tr += '<input class="cmdAttr form-control type input-sm expertModeVisible" data-l1key="configuration" data-l2key="fonction" disabled style="margin-bottom : 5px;" />';
		}			 
		if (init(_cmd.name) == 'Chaine Actuelle') {
		 tr += '<input class="cmdAttr form-control type input-sm expertModeVisible" data-l1key="configuration" data-l2key="chaine_actuelle" disabled style="margin-bottom : 5px;" />';
		}
		tr += '</td>';
		tr += '<td>';		
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
		}
		tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
		tr += '</td>';
		tr += '</tr>';
		$('#table_info tbody').append(tr);
		$('#table_info tbody tr:last').setValues(_cmd, '.cmdAttr');
		if (isset(_cmd.type)) {
			$('#table_info tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
		}
		jeedom.cmd.changeType($('#table_info tbody tr:last'), init(_cmd.subType));
	}
/*
 * MOSAIQUE
 */
	if (init(_cmd.name).indexOf("Mosaique ") != '-1' && init(_cmd.type) == 'action') {
		var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" disabled style="width : 140px;" placeholder="{{Nom}}">';
		tr += '</td>';
		tr += '<td>';
		tr += '<select class="cmdAttr form-control" data-l1key="configuration" data-l2key="mosaique_chaine">';
		tr += '</select>';	
		tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
		}
		tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
		tr += '</td>';
		tr += '</tr>';
		$('#table_mos tbody').append(tr);
		$('#table_mos tbody tr:last').setValues(_cmd, '.cmdAttr');
		var tr = $('#table_mos tbody tr:last');
		
		code_loc = $('.eqLogicAttr[data-l1key=configuration][data-l2key=localisation]').val();
		$.getJSON('plugins/orangetv/core/config/chaines.json', function(chaine) {
			tr.find('.cmdAttr[data-l1key=configuration][data-l2key=mosaique_chaine]').append('<option value="blank">{{ }}</option>');			
			$.each(chaine.localisation,function(index_loc,liste_loc){
				if (code_loc == liste_loc.code) {
					$.each(liste_loc.liste,function(index_chaine,nom_chaine){						
						tr.find('.cmdAttr[data-l1key=configuration][data-l2key=mosaique_chaine]').append('<option value="'+ nom_chaine.logo +'">{{'+ nom_chaine.nom +'}}</option>');
					});
				}
            });
		tr.setValues(_cmd, '.cmdAttr');
		});
		
		if (isset(_cmd.type)) {
			$('#table_mos tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
		}
		jeedom.cmd.changeType($('#table_mos tbody tr:last'), init(_cmd.subType));
	}
}
