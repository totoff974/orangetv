<?php
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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>

<form class="form-horizontal">
    <fieldset>
            <legend><i class="icon loisir-darth"></i> {{Aide au développement}}</legend>
			<span><i>Ce plugin est gratuit, le don est laissé au libre choix de chacun en fonction de sa satisfaction pour m'aider au développement. Merci.</i></span>
			<div class="form-group" align="center">	
                <div align="center">
					<a class="btn" id="bt_paypal" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7BBF45ZDD9Y8L" target="_new" >
					<img src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_donateCC_LG.gif" border="0" alt="{{Faire un don via Paypal au développeur}}">
					<img alt="" border="0" src="https://www.paypalobjects.com/fr_XC/i/scr/pixel.gif" width="1" height="1">
					</a>
               </div>
           </div>		
    </fieldset>
	
    <fieldset>
            <legend><i class="icon loisir-darth"></i> {{Configuration}}</legend>
            <div class="form-group">
                <label class="col-sm-4 control-label">{{Fréquence d'intérogation des décodeurs (en secondes) :}}</label>
                <div class="col-sm-4">
					<input type="number" min="1" max="600" class="configKey form-control" data-l1key="freq_actu"/>
               </div>
           </div>
			<div class="form-group">
				<label class="col-sm-4 control-label">{{Affichage sous forme de Widget :}}</label>
				<div class="col-sm-4">
					<label class="checkbox-inline"><input type="checkbox" class="configKey" data-l1key="widget" checked />{{Activer}}</label>
			   </div>
			</div>			   
    </fieldset>
</form>
<form class="form-horizontal">
    <fieldset>
    <legend><i class="icon loisir-darth"></i> {{Démon}}</legend>

    <div class="form-group expertModeVisible">
        <label class="col-sm-4 control-label">{{Port socket interne}}</label>
        <div class="col-sm-2">
            <input class="configKey form-control" data-l1key="socketport" />
        </div>
    </div>
    <div class="form-group expertModeVisible">
        <label class="col-sm-4 control-label">{{Cycle (s)}}</label>
        <div class="col-sm-2">
            <input class="configKey form-control" data-l1key="cycle" />
        </div>
    </div>
</fieldset>
</form>