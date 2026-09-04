// blockchain-anchor/anchor.js
require("dotenv").config();
const { ethers } = require("ethers");

// ABI = "daftar fungsi" contract yang boleh dipanggil dari luar.
// Cukup fungsi anchorHash saja yang kita butuhkan di sini.
const ABI = ["function anchorHash(bytes32 _rootHash) public"];

async function main() {
    // Ambil hash yang dikirim dari Laravel lewat argumen command line
    const rootHash = process.argv[2];
    if (!rootHash) {
        console.error("Usage: node anchor.js <rootHash>");
        process.exit(1);
    }

    const provider = new ethers.JsonRpcProvider(process.env.SEPOLIA_RPC_URL);
    const wallet = new ethers.Wallet(process.env.PRIVATE_KEY, provider);
    const contract = new ethers.Contract(
        process.env.CONTRACT_ADDRESS,
        ABI,
        wallet,
    );

    const tx = await contract.anchorHash(rootHash);
    console.log("Transaksi terkirim, menunggu konfirmasi...");
    const receipt = await tx.wait();

    // Output dalam format JSON supaya gampang dibaca dari Laravel
    console.log(
        JSON.stringify({
            success: true,
            txHash: receipt.hash,
            blockNumber: receipt.blockNumber,
        }),
    );
}

main().catch((err) => {
    console.log(JSON.stringify({ success: false, error: err.message }));
    process.exit(1);
});
