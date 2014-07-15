<?
interface IObservable {
    public function attach(Observer $observer_in);
    public function detach(Observer $observer_in);
    public function notify($data);
}
?>