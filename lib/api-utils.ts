interface RetryOptions {
  maxRetries?: number;
  initialDelay?: number;
  maxDelay?: number;
  backoffFactor?: number;
}

export async function withRetry<T>(
  fn: () => Promise<T>,
  options: RetryOptions = {}
): Promise<T> {
  const {
    maxRetries = 3,
    initialDelay = 1000,
    maxDelay = 10000,
    backoffFactor = 2,
  } = options;

  let lastError: Error | null = null;
  let delay = initialDelay;

  for (let attempt = 0; attempt <= maxRetries; attempt++) {
    try {
      return await fn();
    } catch (error) {
      lastError = error as Error;
      
      if (attempt === maxRetries) {
        break;
      }

      // Don't retry on certain errors
      if (error instanceof Error) {
        const errorMessage = error.message.toLowerCase();
        if (
          errorMessage.includes('401') ||
          errorMessage.includes('403') ||
          errorMessage.includes('invalid') ||
          errorMessage.includes('authentication')
        ) {
          throw error;
        }
      }

      // Wait before retrying
      await new Promise(resolve => setTimeout(resolve, delay));
      delay = Math.min(delay * backoffFactor, maxDelay);
    }
  }

  throw lastError || new Error('Request failed after retries');
}

interface RateLimitOptions {
  maxRequests?: number;
  windowMs?: number;
}

class RateLimiter {
  private requests: Map<string, number[]> = new Map();
  private maxRequests: number;
  private windowMs: number;

  constructor(options: RateLimitOptions = {}) {
    this.maxRequests = options.maxRequests || 60;
    this.windowMs = options.windowMs || 60000; // 1 minute default
  }

  canMakeRequest(key: string): boolean {
    const now = Date.now();
    const requests = this.requests.get(key) || [];
    
    // Remove old requests outside the window
    const recentRequests = requests.filter(time => now - time < this.windowMs);
    
    if (recentRequests.length >= this.maxRequests) {
      return false;
    }

    recentRequests.push(now);
    this.requests.set(key, recentRequests);
    return true;
  }

  getTimeUntilNextRequest(key: string): number {
    const requests = this.requests.get(key) || [];
    if (requests.length === 0) return 0;

    const oldestRequest = Math.min(...requests);
    const elapsed = Date.now() - oldestRequest;
    return Math.max(0, this.windowMs - elapsed);
  }
}

export const rateLimiter = new RateLimiter({
  maxRequests: 60,
  windowMs: 60000,
});

export async function withRateLimit<T>(
  key: string,
  fn: () => Promise<T>
): Promise<T> {
  if (!rateLimiter.canMakeRequest(key)) {
    const waitTime = rateLimiter.getTimeUntilNextRequest(key);
    await new Promise(resolve => setTimeout(resolve, waitTime));
  }

  return await fn();
}

interface CacheEntry<T> {
  data: T;
  timestamp: number;
  expiresAt: number;
}

class SimpleCache {
  private cache: Map<string, CacheEntry<any>> = new Map();
  private defaultTTL: number;

  constructor(defaultTTL: number = 3600000) {
    // 1 hour default
    this.defaultTTL = defaultTTL;
  }

  set<T>(key: string, data: T, ttl?: number): void {
    const now = Date.now();
    const expiresAt = now + (ttl || this.defaultTTL);

    this.cache.set(key, {
      data,
      timestamp: now,
      expiresAt,
    });
  }

  get<T>(key: string): T | null {
    const entry = this.cache.get(key);
    if (!entry) return null;

    const now = Date.now();
    if (now > entry.expiresAt) {
      this.cache.delete(key);
      return null;
    }

    return entry.data as T;
  }

  clear(key?: string): void {
    if (key) {
      this.cache.delete(key);
    } else {
      this.cache.clear();
    }
  }
}

export const cache = new SimpleCache();

export function logAPIUsage(
  endpoint: string,
  tokensUsed?: number,
  rateLimitRemaining?: number
): void {
  const log = {
    timestamp: new Date().toISOString(),
    endpoint,
    ...(tokensUsed && { tokensUsed }),
    ...(rateLimitRemaining !== undefined && { rateLimitRemaining }),
  };

  console.log('[API Usage]', JSON.stringify(log));
}

