import { loadFromFirestore } from "./main.js";

// Renders branch buttons and handles clicks
async function initBranches() {
    const branches = await loadData('branches.json');
    const families = await loadData('families.json');
    const grid = document.getElementById('branch-grid');
    if (!branches || !grid) return;

    grid.innerHTML = branches.map(branch => {
        // Calculate stats per branch
        const branchFamilies = families ? families.filter(f => f.branch === branch.branchName) : [];
        const familyCount = branchFamilies.length;
        const memberCount = branchFamilies.reduce((acc, curr) => acc + (curr.members ? curr.members.length : 0), 0);

        return `
            <button class="branch-btn" onclick="window.location.href='families.php?branch=${encodeURIComponent(branch.branchName)}'">
                <span>${branch.branchName}</span>
                <span class="branch-count">${familyCount} परिवार | ${memberCount} सदस्य</span>
            </button>
        `;
    }).join('');
}

// Initialize on pages with branch-grid
if (document.getElementById('branch-grid')) {
    initBranches();
}
if (document.getElementById('branch-grid')) {
    initBranches();
}