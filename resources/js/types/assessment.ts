export interface Person {
    id: string | null;
    name: string | null;
    [key: string]: any; // For dynamic meta columns
}

export interface MCQAnswer {
    id: string;
    score: number;
    bubbles: any[][];
}

export interface FreeformAnswer {
    id: string;
    score: number;
}

export interface BlankAnswer {
    id: string;
    score: number;
}

export interface Answers {
    mcq: MCQAnswer[];
    Freeform: FreeformAnswer[];
    Blanks: BlankAnswer[];
}

export interface AssessmentAnswer {
    person: Person;
    answers: Answers;
}

export interface MCQBlock {
    id: string;
    section: string;
}

export interface FreeformBlock {
    id: string;
    Instruction?: string;
}

export interface BlankBlock {
    id: string;
    section?: string;
}

export interface OMRSheet {
    OMRSheet: {
        MCQ: MCQBlock[];
        Freeform?: FreeformBlock[];
        Blanks?: BlankBlock[];
    };
}

export interface ExportOption {
    label: string;
    value: string;
}

export interface ExportSection {
    section: string;
    options: ExportOption[];
}

export type ScoreMap = Record<string, number>;
