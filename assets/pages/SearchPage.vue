<script setup>
import { ref, onMounted, watch } from 'vue';
import { searchCard, fetchAllSetCode } from '../services/cardService';

const cards = ref([]);
const loadingCards = ref(false);
const searchQuery = ref('');
const selectedSetCode = ref('');
const setCodes = ref([]);
const errorMessage = ref('');

async function loadCards(query = '') {
    loadingCards.value = true;
    try {
        cards.value = await searchCard(query, { setCode: selectedSetCode.value });
        errorMessage.value = '';
    } catch (error) {
        errorMessage.value = 'Une erreur est survenue lors du chargement des cartes';
        cards.value = [];
    } finally {
        loadingCards.value = false;
    }
}

watch(searchQuery, (newQuery) => {
    if (newQuery.length >= 3) {
        loadCards(newQuery);
    } else if (newQuery.length === 0) {
        cards.value = [];
    }
});

watch(selectedSetCode, () => {
    loadCards(searchQuery.value);
});

onMounted(async () => {
    try {
        setCodes.value = await fetchAllSetCode();
    } catch (error) {
        errorMessage.value = 'Une erreur est survenue lors du chargement des sets';
    }
});
</script>

<template>
    <div>
        <h1>Rechercher une Carte</h1>
        <div class="search-container">
            <div class="input-group">
                <label for="card-search">Nom de la carte</label>
                <input
                    id="card-search"
                    v-model="searchQuery"
                    type="text"
                    placeholder="Rechercher une carte..."
                    aria-label="Rechercher une carte"
                />
            </div>
            <div class="input-group">
                <label for="set-select">Set</label>
                <select
                    id="set-select"
                    v-model="selectedSetCode"
                    aria-label="Sélectionner un set"
                >
                    <option value="">Tous les sets</option>
                    <option
                        v-for="setCode in setCodes"
                        :key="setCode"
                        :value="setCode"
                    >
                        {{ setCode }}
                    </option>
                </select>
            </div>
        </div>
        <p v-if="errorMessage" class="error-message" role="alert">{{ errorMessage }}</p>
        <div class="card-list">
            <div v-if="loadingCards" role="status">Loading...</div>
            <div v-else-if="cards && cards.length > 0">
                <div class="card" v-for="card in cards" :key="card.id">
                    <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                        {{ card.name }} - {{ card.uuid }}
                    </router-link>
                </div>
            </div>
            <div v-else>
                <p v-if="cards == null">Aucune carte trouvée</p>
                <p v-else>Veuillez entrer au moins 3 characters dans la recherche</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card-list {
    display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    gap: 10px;
}

.card {
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
}

.card:hover {
    background-color: #f0f0f0;
}

.search-container {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.input-group label {
    font-weight: bold;
}

.search-container input {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.search-container select {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.error-message {
    color: red;
    margin: 10px 0;
}
</style>
