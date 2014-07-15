<?

include_once("IObservable.php");

class Observable extends IObservable {
    private $observers = array();
    
    function __construct() {
    }
    
    function attach(Observer $observer_in) {
      //could also use array_push($this->observers, $observer_in);
      $this->observers[] = $observer_in;
    }
    
    function detach(Observer $observer_in) {
      //$key = array_search($observer_in, $this->observers);
      foreach($this->observers as $okey => $oval) {
        if ($oval == $observer_in) { 
          unset($this->observers[$okey]);
        }
      }
    }
    
    function notify($data) {
      foreach($this->observers as $obs) {
        $obs->update($this, $data);
      }
    }
}
?>