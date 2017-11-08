<?php

namespace mecadoapp\control;

use mecadoapp\model\Item as item;
use mecadoapp\model\Liste as liste;
use mecadoapp\model\Acheteur as acheteur;

class ItemController extends \mf\control\AbstractController {


    /* Constructeur :
     * 
     * Appelle le constructeur parent
     *
     * c.f. la classe \mf\control\AbstractController
     * 
     */
    
    public function __construct(){
        parent::__construct();
    }
    
    /**
     * Fonction qui va gèrer l'affichage des cadeaux de la liste, le premier paramètre correspond au message d'erreur des messages
     * 
     * @param e = correspond au message d'erreur des messages, vide par défaut
     */
    public function viewItem($e = null){
    	$get = $this->request->get;
    	
    	$resultat['erreur'] = $e;
    	$resultat['listeItem'] = null;
    	
    	try{
	    	if(isset($get['id'])){
	    		
	    		$util = new \mecadoapp\auth\MecadoAuthentification();
	    		
	    		if (is_null($util->user_login)){
	    			throw new \mf\auth\exception\AuthentificationException("Vous devez être authentifié pour accéder à ce lien");
	    		}
	    		else{
	    			$liste = liste::where('liste.id', '=', $get['id'])
	    			->first();
	    			
	    			//Si la liste n'a pas été trouvé, on retourne une erreur
	    			if(!isset($liste) || $liste->id == null){
	    				throw new \mf\auth\exception\AuthentificationException("L'identifiant de la liste est introuvable");
	    			}
	    			
	    			//On va rechercher si l'utilisateur en session est bien le propriétaire de la liste
	    			if($liste->user->mail != $util->user_login){
	    				throw new \mf\auth\exception\AuthentificationException("Vous n'êtes pas le propriétaire de cette liste");
	    			}
	    			
	    			$listeItem = $liste->items;
	    		}
	    	}
	    	else{
	    		throw new \mf\auth\exception\AuthentificationException("L'identifiant de la liste est introuvable");
	    	}
	    	
	    	$resultat['listeItem']= $listeItem;
    	}catch(\mf\auth\exception\AuthentificationException $e){
    		$resultat['erreur'] = $e->getmessage();
    	}
    	
    	$vue = new \mecadoapp\view\MecadoView($resultat);
    	return $vue->render('item');

    	
    }
    public function addItem(){
        $vue = new \mecadoapp\view\MecadoView(null);
        $vue ->render('addItem'); 
    }

    /**
     * Fonction qui va réserver un item avec les données de l'acheteur
     */
    public function reservItem(){
    	try{
    		if(!is_null($this->request->post)){

    			if(isset($this->request->post['id_item']) && $this->request->post['id_item'] != null){
    				$form=$this->request->post;
    			}
    			else{
    				throw new \mf\auth\exception\AuthentificationException("L'identifiant du cadeau est vide dans le formulaire de réservation");
    			}
    			
    			//on va vérifier que l'item n'est pas déja réservé
    			$item = item::where('id', '=', $form['id_item'])
    			->first();
    			if(isset($item->acheteurs[0]) && $item->acheteurs[0] != null){
    				throw new \mf\auth\exception\AuthentificationException("Le cadeau a déjà été réservé");
    			}
    			
    			$acheteur = new acheteur();
    			$acheteur->nom = $form['nom'];
    			$acheteur->message = $form['message'];
    			$acheteur->id_item = $form['id_item'];
    			$acheteur->save();
    		}
    		
    		$this->viewItem();
    		
    	}catch(\mf\auth\exception\AuthentificationException $e){
    		$this->viewItem($e->getmessage());
    	}
    }

}
