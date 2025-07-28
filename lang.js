// Import required modules
import express from 'express';
import fs from 'fs';
import path from 'path';
import dotenv from 'dotenv';
import { ChatOpenAI } from '@langchain/openai';


// Load environment variables from .env

dotenv.config();
const app = express();
app.use(express.json()); // midleware to parse JSON bodies from incoming requests


// Initialize the OpenAI language model

const llm = new ChatOpenAI({
  model: "gpt-4o-mini",
  temperature: 0,
  apiKey: process.env.OPEN_AI_APIKEY,
});


// Define post endpoint to screen applicants

app.post('/screen', async (req, res) => {
  try {
    const resumeData = req.body; // Get resume and job data from request body

      // Define prompt for the AI model to evaluate applicant 
    const prompt = `
You are an AI hiring screener. Based on the data below, return "true" if the applicant seems reasonably qualified for the job — including related experience, transferable skills, or relevant education. Return "false" only if there is clearly no alignment. Keep it fair but not overly strict.
Example output: true - Applicant has 2 years relevant experience.

DATA:
${JSON.stringify(resumeData, null, 2)}

Respond with only one line:
"true - [short reason]" or "false - [short reason]"
`;
    // Send the prompt to the AI model and get a response
    const response = await llm.invoke([{ role: "user", content: prompt }]);

    //parse the airesponse

    const result = (response.content || "").trim();
    const [decisionPart, reasonPart] = result.split(" - ");
    const decision = decisionPart?.toLowerCase().includes("true") ? "true" : "false";
    const reason = reasonPart?.trim() || "No reason provided";

    //logs
    const timestamp = new Date().toISOString();
    const applicantName = resumeData.applicant?.name || "Unknown";
    const jobTitle = resumeData.job?.title || "Unknown Job";
    const logLine = `[${timestamp}] Applicant: ${applicantName} | Job: ${jobTitle} | Decision: ${decision.toUpperCase()} | Reason: ${reason}\n`;

    fs.appendFileSync(path.join("logs", "ai_reason_log.txt"), logLine);

    // Return the decision
    res.json({ decision, reason });
  } catch (err) {
    console.error("AI Error:", err.message);
    res.status(500).json({ decision: "false", reason: "AI processing error" });
  }
});

app.listen(4000, () => {
  console.log("AI Server running on http://localhost:4000/screen");
});
