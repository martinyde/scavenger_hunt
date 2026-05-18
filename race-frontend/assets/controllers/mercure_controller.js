import { Controller } from "@hotwired/stimulus";

/**
 * Mercure EventSource controller.
 * Subscribes to Mercure topics and refreshes race content on events.
 */
export default class extends Controller {
    static values = {
        url: String,
        raceId: Number,
    };

    connect() {
        this.eventSource = new EventSource(this.urlValue);

        this.eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleEvent(data);
        };

        this.eventSource.onerror = () => {
            // Reconnection is handled automatically by EventSource
        };
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }

    handleEvent(data) {
        // Fetch fresh HTML from the race-frontend partial endpoint
        const raceId = this.raceIdValue;

        if (data.type === "race_state_changed" || data.type === "participant_updated") {
            fetch(`/race/${raceId}/partial/race-content`)
                .then((response) => response.text())
                .then((html) => {
                    const raceContent = document.getElementById(`race-${raceId}`);
                    if (raceContent) {
                        raceContent.innerHTML = html;
                    }
                });
        }

        if (data.type === "participant_added" || data.type === "participant_updated") {
            fetch(`/race/${raceId}/partial/participants`)
                .then((response) => response.text())
                .then((html) => {
                    const participantsList = document.getElementById("participants");
                    if (participantsList) {
                        participantsList.innerHTML = html;
                    }

                    const progressList = document.getElementById("participants-progress-list");
                    if (progressList) {
                        // Re-fetch progress partial
                        fetch(`/race/${raceId}/partial/participants`)
                            .then((r) => r.text())
                            .then((h) => {
                                progressList.innerHTML = h;
                            });
                    }
                });
        }
    }
}
