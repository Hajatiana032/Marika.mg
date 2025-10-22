import {Controller} from '@hotwired/stimulus';
import confetti from 'canvas-confetti';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const flashMessages = document.querySelectorAll('.alert');

        flashMessages.forEach(flash => {
            if (!flash.classList.contains('bg-danger')) {
                confetti({
                    particleCount: 100,
                    spread: 200,
                    origin: {y: 0.6}
                });
            }
        });
    }
}
