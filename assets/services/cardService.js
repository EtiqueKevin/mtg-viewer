export async function fetchAllCards(options) {
    const optionsString = options ? `?${new URLSearchParams(options).toString()}` : '';
    const response = await fetch(`/api/card/all${optionsString}`);
    if (!response.ok) throw new Error('Failed to fetch cards');
    const result = await response.json();
    return result;
}

export async function fetchCard(uuid) {
    const response = await fetch(`/api/card/${uuid}`);
    if (response.status === 404) return null;
    if (!response.ok) throw new Error('Failed to fetch card');
    const card = await response.json();
    card.text = card.text.replaceAll('\\n', '\n');
    return card;
}

export async function searchCard(name, options){
    const optionsString = options ? `?${new URLSearchParams(options).toString()}` : '';
    const response = await fetch(`/api/card/search/${name}${optionsString}`);
    if (response.status === 404) return null;
    if (!response.ok) throw new Error('Failed to fetch cards');
    const result = await response.json();
    return result;
}

export async function fetchAllSetCode(){
    const response = await fetch('/api/card/setCode');
    if (response.status === 404) return null;
    if (!response.ok) throw new Error('Failed to fetch set code');
    const result = await response.json();
    return result;
}
