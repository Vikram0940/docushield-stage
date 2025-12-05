import { driver } from 'driver.js';
import "driver.js/dist/driver.css";

export function initNewDocumentTour() {
    const driverObjNewDocument = driver({
    showProgress: true,
    steps: [
        { element: '.ds-version-history', popover: { title: 'Document Versions', description: 'Check past versions of your documents here.', side: "right", align: 'center' }},
        { element: '.ds-doc-icomment', popover: { title: 'Comments', description: 'Share your thoughts and feedback with your team or clients by adding them as commenters to your documents.', side: "left", align: 'start' }},
        { element: '.ds-doc-review', popover: { title: 'Send for review & signature', description: 'When you\'re all done, you can send it for review. Once all parties agree you can send a singable version.', side: "left", align: 'start' }},
    ]
    });

    driverObjNewDocument.drive();
}